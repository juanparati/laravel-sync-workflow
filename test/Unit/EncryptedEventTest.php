<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Juanparati\SyncWorkflow\Casts\ConditionalCryptCast;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;
use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\TestEventSourcingWorkflow;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class EncryptedEventTest extends SyncWorkflowTestBase
{
    use RefreshDatabase;

    public function test_encrypted_event()
    {
        config(['sync-workflow' => ['encrypt' => true]]);

        $testWorkflow = new TestEventSourcingWorkflow(2);

        $result = SyncExecutor::make()
            ->load($testWorkflow)
            ->run()
            ->getResult();

        $this->assertEquals(8, $result);

        $workflowState = SyncWorkflowState::query()->first();

        $this->assertEquals(TestEventSourcingWorkflow::class, $workflowState->workflow);
        $this->assertInstanceOf(TestEventSourcingWorkflow::class, $workflowState->instance);
        $this->assertEquals(8, $workflowState->result);
        $this->assertTrue($workflowState->was_success);
        $this->assertEquals(0, $workflowState->attempts);

        $rawRecord = \DB::table('sync_workflow_states')->first();
        $this->assertStringStartsWith(ConditionalCryptCast::CRYPT_PREFIX, $rawRecord->instance);
        $this->assertStringStartsWith(ConditionalCryptCast::CRYPT_PREFIX, $rawRecord->activities);
        $this->assertStringStartsWith(ConditionalCryptCast::CRYPT_PREFIX, $rawRecord->result);
    }
}
