<?php

namespace Juanparati\SyncWorkflow\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;


abstract class SyncWorkflowMakeCommandBase extends Command
{

    protected string $stub;

    /**
     * The filesystem instance.
     */
    protected Filesystem $files;


    public function __construct() {
        parent::__construct();
        $this->files = new Filesystem();
    }


    protected function publish(string $name, string $dir) : void {
        $path = app_path($name . DIRECTORY_SEPARATOR . $dir . '.php');

        // Ensure the directory exists
        $this->files->makeDirectory(dirname($path), 0777, true, true);

        // Get stub content
        $stub = $this->files->exists(base_path('stubs/vendor/sync-workflow/workflow.stub'))
            ? $this->files->get(base_path('stubs/vendor/sync-workflow/workflow.stub'))
            : $this->files->get(__DIR__.'/../../../stubs/workflow.stub');

        $this->files->put($path, $this->replaceSubs($stub, $name, $dir));
    }


    abstract protected function replaceSubs(string $stub, string $name, string $dir) : string;
}
