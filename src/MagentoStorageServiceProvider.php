<?php

namespace Grayloon\MagentoStorage;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MagentoStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Get the Telescope route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace' => 'Grayloon\MagentoStorage\Http\Controllers',
        ];
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/Storage/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Database/Migrations' => database_path('migrations'),
            ], 'magento-migrations');

            $this->publishes([
                __DIR__.'/Database/Factories' => database_path('factories'),
            ], 'magento-factories');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->commands([
            Console\SyncMagentoProductsCommand::class,
            Console\SyncMagentoCategoriesCommand::class,
            Console\SyncMagentoCustomersCommand::class,
        ]);
    }
}
