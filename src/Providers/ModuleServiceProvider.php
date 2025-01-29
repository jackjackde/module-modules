<?php

namespace JackJack\Modules\Providers;

use DirectoryIterator;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use JackJack\Framework\Providers\AppServiceProvider;
use JackJack\Modules\Traits\ModuleTrait;

class ModuleServiceProvider extends ServiceProvider
{
    use ModuleTrait;

    /**
     * @var ?string
     */
    protected ?string $moduleName = null;
    /**
     * @var ?string
     */
    protected ?string $moduleNameLower = null;
    protected ?string $routeServiceProviderClass = null;
    protected ?string $eventServiceProviderClass = null;
    protected bool $hasViews = true;
    private Kernel $kernel;

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        if (!$this->isModuleEnabled()) {
            return;
        }
        $this->registerConfig();
        $this->registerDi();
        $this->registerLayout();

        if ($this->routeServiceProviderClass !== null) {
            $this->app->register($this->routeServiceProviderClass);
        }

        if ($this->eventServiceProviderClass !== null) {
            $this->app->register($this->eventServiceProviderClass);
        }

        $this->registerTranslations();

        $this->registerViews();
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $module = $this->getModule();

        if ($module === null) {
            return;
        }

        $dir = $module->path . '/etc/';

        /** @var \DirectoryIterator $item */
        foreach (new DirectoryIterator($dir) as $item) {
            if (str_contains($item->getFilename(), 'config')) {
                $fileName = $item->getFilename();
                if ($item->isFile() && $fileName === 'config.php') {
                    $this->mergeConfigFrom(
                        $item->getPathname(),
                        $this->moduleNameLower
                    );
                } elseif ($item->isDir()) {
                    foreach (new DirectoryIterator($item->getPathname()) as $subItem) {
                        if ($subItem->isFile()) {
                            $configName = str_replace('.php', '', $subItem->getFilename());
                            $this->mergeConfigFrom(
                                $subItem->getPathname(),
                                $configName
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function registerDi(): void
    {
        $diFile = app_path($this->moduleName . '/etc/di.php');
        if (file_exists($diFile)) {

            $diConfig = require_once $diFile;

            if (is_array($diConfig)) {
                foreach ($diConfig as $type => $objects) {
                    foreach ($objects as $class => $interface) {
                        if ($type == AppServiceProvider::DI_TYPE_DEFAULT) {
                            $this->app->bind($interface, $class);
                        } elseif ($type == AppServiceProvider::DI_TYPE_SINGLETON) {
                            $this->app->singleton($interface, $class);
                        }
                    }
                }
            }
        }
    }

    /**
     * Register Layout.
     */
    protected function registerLayout(): void
    {
        $layoutFile = app_path($this->moduleName . '/etc/layout.php');
        if (file_exists($layoutFile)) {
            $this->mergeConfigFrom(
                $layoutFile,
                'layout.' . $this->moduleNameLower
            );
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $moduleLangPath = app_path($this->moduleName . '/i18n');

        if (is_dir($moduleLangPath)) {
            $this->loadTranslationsFrom($moduleLangPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($moduleLangPath);
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        if ($this->hasViews) {
            $paths = [];
            if (is_dir(app_path($this->moduleName . '/views'))) {
                $paths[] = app_path($this->moduleName . '/views');
            }

            if (is_dir(app_path($this->moduleName . '/views/base'))) {
                $paths[] = app_path($this->moduleName . '/views/base');
            }

            $this->loadViewsFrom($paths, $this->moduleNameLower);

            Blade::componentNamespace("JackJack\\$this->moduleName\\View\\Components", $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Boot the application events.
     *
     * @throws \Exception
     */
    public function boot(): void
    {
        if (!$this->isModuleEnabled()) {
            return;
        }
        $this->loadMigrationsFrom(app_path($this->moduleName . '/Database/Migrations'));
    }

//    protected function getKernel(): Kernel
//    {
//        if (!isset($this->kernel)) {
//            $this->kernel = resolve(Kernel::class);
//        }
//
//        return $this->kernel;
//    }
}
