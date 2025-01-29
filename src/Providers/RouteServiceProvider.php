<?php

namespace JackJack\Modules\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use JackJack\Modules\Traits\ModuleTrait;

class RouteServiceProvider extends ServiceProvider
{
    use ModuleTrait;

    /**
     * The path to the "home" route for your application.
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const string HOME = 'dashboard';
    public const string HOME_ROUTE = 'dashboard.index.index';

    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'JackJack\Framework\Http\Controllers';

    protected ?string $moduleName = null;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        if (!$this->isModuleEnabled()) {
            return;
        }
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        if (!$this->isModuleEnabled()) {
            return;
        }
        if ($this->moduleName !== null) {
            $this->mapModuleApiRoutes();
            $this->mapModuleWebRoutes();
        } else {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
        }
    }

    /**
     * Define the "api" routes for the application.
     * These routes are typically stateless.
     */
    protected function mapModuleApiRoutes(): void
    {
        $module = $this->getModule();
        if (null === $module) {
            return;
        }
        if (file_exists($module->path . '/etc/routes/api.php')) {
            Route::prefix('api')
                 ->middleware('api')
                 ->namespace($this->getModuleNamespace())
                 ->group($module->path . '/etc/routes/api.php');
        }
    }

    protected function getModuleNamespace(): string
    {
        if (!isset($this->moduleName)) {
            return '';
        }

        return $this->getModule()->getControllerNamespace();
    }

    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapModuleWebRoutes(): void
    {
        $module = $this->getModule();
        if (null === $module) {
            return;
        }
        if (file_exists($module->path . '/etc/routes/web.php')) {
            Route::middleware('web')
                 ->namespace($this->getModuleNamespace())
                 ->group($module->path . '/etc/routes/web.php');
        }
    }

    protected function mapApiRoutes(): void
    {
        if (file_exists(base_path('routes/api.php'))) {
            Route::middleware('api')
                 ->prefix('api')
                 ->group(base_path('routes/api.php'));
        }
    }

    protected function mapWebRoutes(): void
    {
        if (file_exists(base_path('routes/web.php'))) {
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        }
    }
}
