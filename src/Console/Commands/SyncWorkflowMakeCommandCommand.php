<?php

namespace Juanparati\SyncWorkflow\Console\Commands;


class SyncWorkflowMakeCommandCommand extends SyncWorkflowMakeCommandBase
{
    protected $signature = 'make:sync-workflow {workflow}
        {--event-sourcing : Add event sourcing support}
        {--workflow-dir=SyncWorkflows : Base path for workflow files}
    ';

    protected $description = 'Create workflow file';

    protected string $stubName = 'syncworkflow.stub';


    public function handle()
    {
        $workflowName = $this->argument('workflow');
        $workflowDir = $this->option('workflow-dir');

        $this->publish($workflowDir, $workflowName);

        $this->info('Workflow created successfully.');
    }

    protected function replaceSubs(string $stub, string $baseDir, string $name): string
    {
        $namespace = 'App\\' . str_replace('/', '\\', $baseDir);

        // Handle nested names like "SubDir/MyWorkflow"
        if (str_contains($name, '/')) {
            $parts = explode('/', $name);
            $className = array_pop($parts);
            $namespace .= '\\' . implode('\\', $parts);
        } else {
            $className = $name;
        }

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ implements }}'],
            [
                $namespace,
                $className,
                $this->option('event-sourcing') ? 'implements WithEventSourcing' : '',
            ],
            $stub
        );
    }
}
