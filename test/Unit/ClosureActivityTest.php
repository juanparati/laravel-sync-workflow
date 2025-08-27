<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\ClosureActivitiesTestWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\ClosureTestWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class ClosureActivityTest extends SyncWorkflowTestBase
{
    public function test_simple_workflow()
    {
        $result = SyncExecutor::make()
            ->load(new ClosureTestWorkflow('foo'))
            ->run()
            ->getResult();

        $this->assertEquals('testfoo', $result);
    }

    public function test_workflow_with_chained_activities()
    {
        $result = SyncExecutor::make()
            ->load(new ClosureActivitiesTestWorkflow('foo'))
            ->run()
            ->getResult();

        $this->assertEquals('testfoo', $result);
    }
}
