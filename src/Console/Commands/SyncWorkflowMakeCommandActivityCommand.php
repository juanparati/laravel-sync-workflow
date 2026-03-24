<?php

namespace Juanparati\SyncWorkflow\Console\Commands;


class SyncWorkflowMakeCommandActivityCommand extends SyncWorkflowMakeCommandBase
{
    protected $signature = 'make:sync-workflow-activity {activity}
        {--workflow-dir=SyncWorkflows : Base path for workflow files}
    ';

    protected $description = 'Create activity file';

    protected string $stubName = 'syncactivity.stub';

    public function handle()
    {
        $name = $this->argument('activity');
        $workflowDir = $this->option('workflow-dir');

        $this->publish($workflowDir, $name);

        $this->info('Activity created successfully.');
    }

    protected function replaceSubs(string $stub, string $baseDir, string $name): string
    {
        $namespace = 'App\\' . str_replace('/', '\\', $baseDir);

        // Handle nested names like "MyWorkflow/MyFirstActivity"
        if (str_contains($name, '/')) {
            $parts = explode('/', $name);
            $className = array_pop($parts);
            $namespace .= '\\' . implode('\\', $parts);
        } else {
            $className = $name;
        }

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [
                $namespace,
                $className,
            ],
            $stub
        );
    }
}
