<?php

namespace Juanparati\SyncWorkflow\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;


abstract class SyncWorkflowMakeCommandBase extends Command
{

    protected string $stubName;

    /**
     * The filesystem instance.
     */
    protected Filesystem $files;


    public function __construct() {
        parent::__construct();
        $this->files = new Filesystem();
    }


    protected function publish(string $baseDir, string $name) : void {
        $path = app_path($baseDir . DIRECTORY_SEPARATOR . $name . '.php');

        // Ensure the directory exists
        $this->files->makeDirectory(dirname($path), 0777, true, true);

        // Get stub content
        $stub = $this->files->exists(base_path('stubs/vendor/sync-workflow/' . $this->stubName))
            ? $this->files->get(base_path('stubs/vendor/sync-workflow/' . $this->stubName))
            : $this->files->get(__DIR__.'/../../../stubs/' . $this->stubName);

        $this->files->put($path, $this->replaceSubs($stub, $baseDir, $name));
    }


    abstract protected function replaceSubs(string $stub, string $baseDir, string $name) : string;
}
