<?php

namespace JackJack\Modules\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use JackJack\Modules\Macros\InertiaModules;
use JackJack\Modules\Modules;

class ModulesServiceProvider extends ServiceProvider
{

    protected array $commands = [
        \JackJack\Modules\Commands\MakeModule::class,
        \JackJack\Modules\Commands\EnableModule::class,
        \JackJack\Modules\Commands\DisableModule::class,
        \JackJack\Modules\Commands\RegisterModuleRoutes::class,
        \JackJack\Modules\Commands\RefreshModulesCache::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'jackjack');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'jackjack');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->bootForResource();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/modules.php', 'modules');

        // Register the service the package provides.
        $this->app->singleton('modules', function ($app) {
            return new Modules;
        });

        $this->commands($this->commands);
        $this->registerModules();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['modules'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php'),
        ], 'modules.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/jackjack'),
        ], 'modules.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/jackjack'),
        ], 'modules.assets');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/jackjack'),
        ], 'modules.lang');*/

        // Registering package commands.
        // $this->commands([]);
    }

    /**
     * @return void
     */
    protected function bootForResource(): void
    {
        Inertia::macro('module', function ($view, $args = []) {
            return Inertia::render(new InertiaModules()->build($view), $args);
        });
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerModules(): void
    {
        $modules = $this->app->make('modules')->all();

        foreach ($modules as $module) {
            if ($module->isActive()) {
                if ($module->hasServiceProvider()) {
                    if (empty($this->app->getProviders($module->getServiceProvider()))) {
                        $this->app->register($module->getServiceProvider());
                    }
                }
            }
        }
    }
}
