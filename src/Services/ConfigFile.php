<?php

declare(strict_types=1);

namespace JackJack\Modules\src\Services;

use Exception;
use Illuminate\Support\Facades\File;
use JsonException;

abstract class ConfigFile
{
    protected string $configFile = '';

    protected string $configFilePath = '';

    /**
     * @throws Exception
     */
    public function __construct(
        string $configFilePath = null
    )
    {
        if (null === $configFilePath) {
            $this->configFilePath = base_path($this->configFile);
        }

        if (!File::exists($this->configFilePath)) {
            throw new Exception("File {$this->configFilePath} does not exist.");
        }

        $this->aliases = $this->readAliases();
        $test = $this->aliases;
    }

    /**
     * @var array $aliases
     */
    protected array $aliases;

    /**
     * @return array
     */
    protected function readAliases(): array
    {
        $content = File::get($this->configFilePath);

        if ($this->isJs()) {
            $content = trim(str_replace(['export default', ';'], '', $content));
            $content = str_replace("'", '"', $content);
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return File::extension($this->configFilePath) === 'json';
    }

    /**
     * @return bool
     */
    public function isJs(): bool
    {
        return File::extension($this->configFilePath) === 'js';
    }

    /**
     * Add alias to the aliases list.
     *
     * @param string $alias
     * @param string|array $path
     * @return $this
     */
    public function addAlias(string $alias, string|array $path): self
    {
        $this->aliases[$alias] = $path;
        return $this;
    }

    /**
     * Remove alias from the aliases list.
     *
     * @param string $alias
     * @return $this
     */
    public function removeAlias(string $alias): self
    {
        unset($this->aliases[$alias]);
        return $this;
    }

    /**
     * Save aliases to the file.
     */
    public function saveAliases(): void
    {
        if ($this->isJs()) {
            $content = "export default " . json_encode($this->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ";";
        } else {
            $content = json_encode($this->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        File::put($this->configFilePath, $content);
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        return $this->aliases;
    }
}
