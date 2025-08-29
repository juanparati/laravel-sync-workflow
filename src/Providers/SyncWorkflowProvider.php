<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Providers;

use Illuminate\Support\ServiceProvider;
use Juanparati\SyncWorkflow\Console\Commands\SyncWorkflowReplayCommand;
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
            __DIR__.'/../../config/sync-workflow.php',
            'sync-workflow'
        );

        if (config('app.env') == 'testing' && env('LIB_PROJECT') == 'laravel-sync-workflow') {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }

        if ($this->app->runningInConsole()) {

            $this->commands([
                SyncWorkflowReplayCommand::class,
                SyncWorkflowViewCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../../config/sync-workflow.php' => config_path('sync-workflow.php'),
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'laravel-sync-workflow');
        }
    }
}
