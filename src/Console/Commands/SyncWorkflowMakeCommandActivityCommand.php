<?php

namespace Juanparati\SyncWorkflow\Console\Commands;


use Illuminate\Filesystem\Filesystem;

class SyncWorkflowMakeCommandActivityCommand extends SyncWorkflowMakeCommandBase
{
    protected $signature = 'make:sync-workflow-activity {activity}
        {--workflow-dir=SyncWorkflows : Base path for workflow files}            
    ';

    protected $description = 'Create activity file';

    public function __construct() {
        parent::__construct();
        $this->files = new Filesystem();
    }

    public function handle()
    {
        $name = $this->argument('activity');
        $workflowDir = $this->option('workflow-dir');

        $this->publish($workflowDir, $name);

        $this->info('Activity created successfully.');
    }

    protected function replaceSubs(string $stub, string $name, string $dir): string
    {
        // Replace stub variables
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [
                'App\\' . str_replace('/', '\\', $dir),
                $name,
            ],
            $stub
        );

        return $stub;
    }
}
