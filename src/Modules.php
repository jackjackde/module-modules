<?php

namespace JackJack\Modules;

use Illuminate\Support\Facades\File;
use JackJack\Modules\src\Services\TsConfig;
use JackJack\Modules\src\Services\ViteConfig;

class Modules
{
    public const string MODULE_CACHE_PATH = 'bootstrap/cache/module-packages.php';
    public const string CONTROLLER_CACHE_PATH = 'bootstrap/cache/module-controllers.php';
    protected string $moduleCachePath;
    protected string $controllerCachePath;

    protected array $modules = [];

    /**
     * Modules constructor.
     */
    public function __construct()
    {
        $this->moduleCachePath = base_path(self::MODULE_CACHE_PATH);
        $this->controllerCachePath = base_path(self::CONTROLLER_CACHE_PATH);
    }

    /**
     * @return void
     */
    public function scan(): void
    {
        $this->modules = [];
        $this->registerController();
    }

    /**
     * Get all modules.
     *
     * @return Module[]
     */
    public function all(): array
    {
        if (empty($this->modules)) {
            $this->modules = $this->getFromCache();
        }

        return $this->modules;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->all()[$name]);
    }

    /**
     * @param string $name
     * @return Module|null
     */
    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Get modules from cache.
     *
     * @return array
     */
    private function getFromCache(): array
    {
        if (!File::exists($this->moduleCachePath)) {
            return [];
        }

        return include $this->moduleCachePath;
    }

    public function register(Module $module): self
    {
        $this->all();
        $this->modules[$module->name] = $module;
        $this->cache();
        return $this;
    }

    /**
     * Store the module.
     *
     * @return Modules
     */
    public function cache(): self
    {
        $modules = $this->all();
        File::put($this->moduleCachePath, '<?php return ' . var_export($modules, true) . ';');
        return $this;
    }

    /**
     * @param string $name
     * @return Module|null
     */
    public function get(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @param string $name
     * @return void
     */
    public function disable(string $name): void
    {
        $module = $this->get($name);
        if ($module) {
            $module->disable();
            $this->cache();
            $this->afterSetup();
        }
    }

    /**
     * @param string $name
     * @return void
     */
    public function enable(string $name): void
    {
        $module = $this->get($name);
        if ($module) {
            $module->enable();
            $this->cache();
            $this->afterSetup();
        }
    }

    /**
     * @return void
     */
    public function registerController(): void
    {
        $controllerResolver = new ControllerResolver();
        foreach ($this->all() as $module) {
            $controllerDir = $module->getControllerPath();
            $apiControllerDir = $module->getApiControllerPath();

            if (is_dir($controllerDir)) {
                foreach (File::directories($controllerDir) as $directory) {

                    if (str_contains($directory, $apiControllerDir)) {
                        $controllerResolver->add($module, $directory, true);
                    } else {
                        $controllerResolver->add($module, $directory);
                    }
                }
            }
        }

        File::put(
            $this->controllerCachePath,
            '<?php return ' . var_export($controllerResolver->toArray(), true) . ';'
        );
    }

    /**
     * @return array
     */
    public function getControllerDirectories(): array
    {
        if (!file_exists($this->controllerCachePath)) {
            return [];
        }

        return include $this->controllerCachePath;
    }

    /**
     * @return void
     */
    public function registerViteConfig(): void
    {
        $viteConfig = new ViteConfig();
        foreach ($this->all() as $module) {
            $viteConfig->addAlias(
                $module->getModuleJsAlias(),
                $module->getModuleJsAliasPath()
            );
        }

        $viteConfig->saveAliases();
    }

    /**
     * @return void
     */
    public function registerTsConfig(): void
    {
        $tsConfig = new TsConfig();
        foreach ($this->all() as $module) {
            $tsConfig->addAlias(
                $module->getModuleTsAlias(),
                $module->getModuleTsAliasPath()
            );
        }

        $tsConfig->saveAliases();
    }

    /**
     * @return void
     */
    public function afterSetup(): void
    {
        $this->registerController();
        $this->registerViteConfig();
        $this->registerTsConfig();
    }
}
