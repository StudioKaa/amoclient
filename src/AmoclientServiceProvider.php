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

        if(config('amoclient.use_migration'))
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
        $this->mergeConfigFrom(
            __DIR__.'/config/amoclient.php', 'amoclient'
        );

        $this->app->make('StudioKaa\Amoclient\AmoclientController');
        $this->app->singleton('StudioKaa\AmoAPI', function () {
            return new AmoAPI();
        });
    }   
}
