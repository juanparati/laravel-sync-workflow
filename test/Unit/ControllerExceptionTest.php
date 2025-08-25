<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowControlledException;
use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestControlledExceptionWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class ControllerExceptionTest extends SyncWorkflowTestBase
{
    public function test_controlled_exception()
    {
        $e = SyncExecutor::make()
            ->load(new TestControlledExceptionWorkflow())
            ->start()
            ->getResult();


        $this->assertInstanceOf(SyncWorkflowControlledException::class, $e);
    }
}
