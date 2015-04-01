<?php namespace Kodeine\Acl;

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
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishMigration();
    }

    /**
     * Publish the config file to the application config directory.
     */
    public function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/acl.php' => config_path('acl.php'),
        ], 'config');
    }

    /**
     * Publish the migration to the application migration folder.
     */
    public function publishMigration()
    {
        $this->publishes([
            __DIR__.'/../../migrations/' => base_path('/database/migrations'),
        ], 'migrations');
    }
}
