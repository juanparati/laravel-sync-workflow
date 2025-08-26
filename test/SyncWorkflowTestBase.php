<?php

namespace Juanparati\SyncWorkflow\Test;

use Juanparati\SyncWorkflow\Providers\SyncWorkflowProvider;
use Orchestra\Testbench\TestCase;

abstract class SyncWorkflowTestBase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app' => [
            'cipher' => 'AES-256-CBC',
            'key' => 'base64:NTR6OXluaW50azZwM3J3d3phdDc5cHRiNXBlMjhuNjU=',
        ]]);
    }

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
