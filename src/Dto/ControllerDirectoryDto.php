<?php

declare(strict_types=1);

namespace JackJack\Modules\Dto;

use Illuminate\Support\Str;
use JackJack\Framework\Const\ConfigPath;
use JackJack\Modules\Module;
use Spatie\LaravelData\Data;

/**
 * Class ControllerDirectoryDto
 */
class ControllerDirectoryDto extends Data
{
    /**
     * @var string|null $dirNamespace
     */
    private ?string $dirNamespace = null;

    /**
     * @param Module $module
     * @param string $directory
     * @param bool $isApi
     */
    public function __construct(
        private readonly Module $module,
        private readonly string $directory,
        private readonly bool $isApi
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $apiPrefix = $this->getApiPrefix();
        $namespace = $this->getNamespace();

        $middlewares = config('modules.middlewares.' . ($this->isApi ? 'api' : 'web'), []);
        $prefix = collect([
            $apiPrefix,
            $this->module->getPackageLower(),
            Str::kebab($this->getDirNamespace()),
        ])->implode('/');

        return [
            'namespace' => $namespace,
            'patterns' => ['*.php'],
            'not_patterns' => ['*Test.php'],
            'prefix' => $this->isApi ? $prefix : null,
            'middleware' => $middlewares,
        ];
    }

    /**
     * @return string
     */
    private function getApiPrefix(): string
    {
        return trim(config(ConfigPath::APP__API__URL_PREFIX), '/');
    }

    /**
     * @return string
     */
    private function getDirNamespace(): string
    {
        if (null === $this->dirNamespace) {
            $this->dirNamespace = trim(strrchr($this->directory, '/'), '/');
        }

        return $this->dirNamespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        $dirNamespace = $this->getDirNamespace();

        if ($this->isApi) {
            return $this->module->getApiControllerNamespace() . '\\' . $dirNamespace;
        } else {
            return $this->module->getControllerNamespace() . '\\' . $dirNamespace;
        }
    }

    /**
     * @return string
     */
    public function getControllerPath(): string
    {
        $dirNamespace = $this->getDirNamespace();
        if ($this->isApi) {
            return $this->module->getApiControllerPath() . '/' . $dirNamespace;
        } else {
            return $this->module->getControllerPath() . '/' . $dirNamespace;
        }
    }
}
