<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowControlledException;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;
use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestControlledExceptionEventSourcingWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestEventSourcingWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestRelativeTimeEventSourcingWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class EventSourceTest extends SyncWorkflowTestBase
{
    use RefreshDatabase;

    public function test_event_sourcing_workflow()
    {
        $testWorkflow = new TestEventSourcingWorkflow(1);

        $result = SyncExecutor::make()
            ->load($testWorkflow)
            ->run()
            ->getResult();

        $this->assertEquals(6, $result);

        $workflowState = SyncWorkflowState::query()->first();

        $this->assertEquals(TestEventSourcingWorkflow::class, $workflowState->workflow);
        $this->assertInstanceOf(TestEventSourcingWorkflow::class, $workflowState->instance);
        $this->assertEquals(6, $workflowState->result);
        $this->assertTrue($workflowState->was_success);
        $this->assertEquals(0, $workflowState->attempts);
    }

    public function test_event_sourcing_time_workflow()
    {
        $testWorkflow = new TestRelativeTimeEventSourcingWorkflow;

        $result = SyncExecutor::make()
            ->load($testWorkflow)
            ->run()
            ->getResult();

        sleep(2);

        $resultReplayed = SyncWorkflowState::query()
            ->first()
            ->replay()
            ->getResult();

        $this->assertEquals($result->toDateTimeString(), $resultReplayed->toDateTimeString());
        $this->assertTrue($resultReplayed->lt(now()));
    }

    public function test_event_sourcing_workflow_with_chained_activities()
    {
        $workflow = new TestControlledExceptionEventSourcingWorkflow(1);

        $e = SyncExecutor::make()
            ->load($workflow)
            ->run()
            ->getResult();

        $this->assertInstanceOf(SyncWorkflowControlledException::class, $e);

        $this->assertDatabaseHas('sync_workflow_states', [
            'workflow' => TestControlledExceptionEventSourcingWorkflow::class,
            'attempts' => 0,
            'was_success' => true,
        ]);

        $this->assertCount(1, $e->getErrors());
    }
}
