<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestActivitiesWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class ActivityTest extends SyncWorkflowTestBase
{
    public function test_simple_workflow()
    {
        $result = SyncExecutor::make()
            ->load(new TestWorkflow(1))
            ->run()
            ->getResult();

        $this->assertEquals(2, $result);
    }

    public function test_workflow_with_chained_activities()
    {
        $result = SyncExecutor::make()
            ->load(new TestActivitiesWorkflow(2))
            ->run()
            ->getResult();

        $this->assertEquals(5, $result);
    }
}
