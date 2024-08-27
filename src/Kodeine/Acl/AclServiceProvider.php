<?php

namespace Kodeine\Acl;

use Blade;
use Illuminate\Support\ServiceProvider;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/acl.php' => config_path('acl.php')
            ],
            'config'
        );

        $this->publishes(
            [
                __DIR__ . '/../../migrations/' => base_path('/database/migrations')
            ],
            'migrations'
        );

        $this->registerBladeDirectives();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/acl.php', 'acl'
        );
    }

    public function registerBladeDirectives()
    {
        Blade::directive('role', function ($expression) {
            return "<?php if (Auth::guard(config('acl.guard'))->check() && Auth::guard(config('acl.guard'))->user()->hasRole({$expression})): ?>";
        });

        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        Blade::directive('permission', function ($expression) {
            return "<?php if (Auth::guard(config('acl.guard'))->check() && Auth::guard(config('acl.guard'))->user()->hasPermission({$expression})): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
