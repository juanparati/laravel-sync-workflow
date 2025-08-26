<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class SyncWorkflowStateTest extends SyncWorkflowTestBase
{
    use RefreshDatabase;

    public function test_model_uses_default_table_name_when_not_configured()
    {
        config(['sync-workflow.table_name' => null]);

        $model = new SyncWorkflowState;

        $this->assertEquals('sync_workflow_states', $model->getTable());
    }

    public function test_replay_throws_exception_when_no_id()
    {
        $model = new SyncWorkflowState;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Workflow state not found.');

        $model->replay();
    }

    public function test_replay_executes_workflow()
    {
        $workflow = new TestWorkflow(5);

        $state = new SyncWorkflowState;
        $state->id = 'test-id';
        $state->workflow = 'TestWorkflow';
        $state->instance = $workflow;
        $state->activities = [];
        $state->result = null;
        $state->errors = null;
        $state->was_success = null;
        $state->attempts = 0;
        $state->first_started_at = now();
        $state->started_at = now();
        $state->finished_at = null;
        $state->save();

        $executor = $state->replay();

        $this->assertEquals(6, $executor->getResult());
    }
}
