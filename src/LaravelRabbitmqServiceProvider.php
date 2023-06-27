<?php

namespace Ex3mm\LaravelRabbitmq;

use Illuminate\Support\ServiceProvider;

class LaravelRabbitmqServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
//        // Публикация миграции
//        $this->publishes([
//            __DIR__.'/database/migrations/' => database_path('migrations')
//        ], 'migration');
//
//        // Публикация модели
//        $this->publishes([
//            __DIR__.'/Models/' => app_path('Models')
//        ], 'models');
    }
}