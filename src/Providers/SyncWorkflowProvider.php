<?php
declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Providers;

use Illuminate\Support\ServiceProvider;
use Juanparati\SyncWorkflow\Console\Commands\SyncWorkflowViewCommand;

class SyncWorkflowProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Merge configuration after publishing
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-sync-workflow.php',
            'laravel-sync-workflow'
        );

        if ($this->app->runningInConsole()) {

            $this->commands([
                SyncWorkflowViewCommand::class
            ]);

            $this->publishes([
                __DIR__ . '/../../config/laravel-sync-workflow.php' => config_path('laravel-sync-workflow.php'),
                __DIR__ . '/../../database/migrations'              => database_path('migrations')
            ], 'laravel-sync-workflow');
        }
    }

}
