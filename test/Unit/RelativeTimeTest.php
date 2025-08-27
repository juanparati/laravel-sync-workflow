<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestRelativeTimeWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class RelativeTimeTest extends SyncWorkflowTestBase
{
    public function test_relative_time()
    {
        $result = SyncExecutor::make()
            ->load(new TestRelativeTimeWorkflow)
            ->run()
            ->getResult();

        $this->assertEquals(now()->toDateTimeString(), $result->toDateTimeString());
    }
}
