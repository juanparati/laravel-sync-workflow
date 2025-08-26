<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;

use Juanparati\SyncWorkflow\SyncWorkflow;

class ClosureTestWorkflow extends SyncWorkflow
{
    public function __construct(protected string $myParam) {}

    public function handle()
    {
        return $this->executor()->runActivity(fn () => 'test'.$this->myParam);
    }
}
