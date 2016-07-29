<?php namespace Kodeine\Acl;

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
        $this->publishConfig();
        $this->publishMigration();

        $laravel = app();
        if (starts_with($laravel::VERSION, '5.0')) {
            $this->registerBlade50();
        } else {
            $this->registerBlade51();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/acl.php',
            'acl'
        );
    }

    /**
     * Publish the config file to the application config directory
     */
    public function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/acl.php' => config_path('acl.php'),
        ], 'config');
    }

    /**
     * Publish the migration to the application migration folder
     */
    public function publishMigration()
    {
        $this->publishes([
            __DIR__ . '/../../migrations/' => base_path('/database/migrations'),
        ], 'migrations');
    }

    /**
     * Register Blade Template Extensions for >= L5.1
     */
    protected function registerBlade51()
    {
        // role
        Blade::directive('role', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->is{$expression}): ?>";
        });

        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        Blade::directive('permission', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->can{$expression}): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Register Blade Template Extensions for <= L5.0
     */
    protected function registerBlade50()
    {
        $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
        $blade->extend(function ($view, $compiler) {
            $pattern = $compiler->createMatcher('role');
            return preg_replace($pattern, '<?php if (Auth::check() && Auth::user()->is$2): ?> ', $view);
        });

        $blade->extend(function ($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('endrole');
            return preg_replace($pattern, '<?php endif; ?>', $view);
        });

        $blade->extend(function ($view, $compiler) {
            $pattern = $compiler->createMatcher('permission');
            return preg_replace($pattern, '<?php if (Auth::check() && Auth::user()->can$2): ?> ', $view);
        });

        $blade->extend(function ($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('endpermission');
            return preg_replace($pattern, '<?php endif; ?>', $view);
        });
    }
}
