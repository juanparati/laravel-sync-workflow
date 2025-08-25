<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;

use Juanparati\SyncWorkflow\SyncWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\ControlledExceptionActivity;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\MultiplyTwoActivity;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\SumOneActivity;

class TestControlledExceptionEventSourcingWorkflow extends SyncWorkflow implements \Juanparati\SyncWorkflow\Contracts\WithEventSourcing
{
    public function __construct(protected int|float $myParam) {}

    public function handle()
    {
        return $this->executor()->runChainedActivities([
            MultiplyTwoActivity::class,
            SumOneActivity::class,
            ControlledExceptionActivity::class,
        ], $this->myParam);
    }
}
