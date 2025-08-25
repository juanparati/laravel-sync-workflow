<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;

use Juanparati\SyncWorkflow\SyncWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\MultiplyTwoActivity;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\SumOneActivity;

class TestActivitiesWorkflow extends SyncWorkflow
{
    public function __construct(protected int|float $myParam) {}

    public function handle()
    {
        return $this->executor()->runChainedActivities([
            MultiplyTwoActivity::class,
            SumOneActivity::class,
        ], $this->myParam);
    }
}
