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
        $this->registerBladeExtensions();
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
     * Register Blade Template Extensions.
     */
    protected function registerBladeExtensions()
    {
        // role
        Blade::directive('role', function($expression) {
            return "<?php if (Auth::check() && Auth::user()->is{$expression}): ?>";
        });

        Blade::directive('endrole', function() {
            return "<?php endif; ?>";
        });

        // permission
        Blade::directive('access', function($expression) {
            return "<?php if (Auth::check() && Auth::user()->can{$expression}): ?>";
        });

        Blade::directive('endaccess', function() {
            return "<?php endif; ?>";
        });
    }
}
