<?php namespace Kodeine\Acl;

use Illuminate\Support\ServiceProvider;

class AclServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/acl.php' => config_path('acl.php'),
            __DIR__ . '/../../migrations/'    => base_path('/database/migrations'),
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
