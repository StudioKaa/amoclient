<?php
namespace StudioKaa\Amoclient;
use Illuminate\Support\ServiceProvider;

class AmoclientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if(env('AMO_USE_MIGRATION', 'yes') == 'yes')
        {
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }   
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('StudioKaa\Amoclient\AmoclientController');
    }
}