<?php

namespace Juanparati\SyncWorkflow\Console\Commands;


class SyncWorkflowMakeCommandCommand extends SyncWorkflowMakeCommandBase
{
    protected $signature = 'make:sync-workflow {workflow}
        {--event-sourcing : Add event sourcing support}
        {--workflow-dir=SyncWorkflows : Base path for workflow files}            
    ';

    protected $description = 'Create workflow file';


    public function handle()
    {
        $workflowName = $this->argument('workflow');
        $workflowDir = $this->option('workflow-dir');

        $this->publish($workflowDir, $workflowName);

        $this->info('Workflow created successfully.');
    }

    protected function replaceSubs(string $stub, string $name, string $dir): string
    {
        // Replace stub variables
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ implements }}'],
            [
                'App\\' . str_replace('/', '\\', $dir),
                $name,
                $this->option('event-sourcing') ? 'implements WithEventSourcing' : ''
            ],
            $stub
        );

        return $stub;
    }
}
