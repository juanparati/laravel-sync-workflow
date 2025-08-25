<?php

namespace Juanparati\SyncWorkflow\Test;

use Juanparati\SyncWorkflow\Providers\SyncWorkflowProvider;
use Orchestra\Testbench\TestCase;

abstract class SyncWorkflowTestBase extends TestCase
{
    /**
     * Load service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [SyncWorkflowProvider::class];
    }

}
