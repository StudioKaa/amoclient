<?php
namespace StudioKaa\amoclient;
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