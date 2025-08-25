<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;

use Juanparati\SyncWorkflow\SyncWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\SumOneActivity;

class TestWorkflow extends SyncWorkflow
{
    public function __construct(protected int|float $myParam) {}

    public function handle()
    {
        return $this->executor()->runActivity(SumOneActivity::class, [$this->myParam]);
    }
}
