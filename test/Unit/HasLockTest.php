<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\Concerns\HasLock;
use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowLockException;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class HasLockTest extends SyncWorkflowTestBase
{
    protected function createHasLockInstance(): object
    {
        return new class {
            use HasLock;

            public function runLock(): void
            {
                $this->onRunHasLock();
            }

            public function endLock(): void
            {
                $this->onEndedHasLock();
            }

            public function failLock(): void
            {
                $this->onRunActivityFailHasLock();
            }

            public function getLock()
            {
                return $this->lock;
            }
        };
    }

    protected function createHasLockInstanceWithUniqueId(string $id): object
    {
        return new class($id) {
            use HasLock;

            public function __construct(private string $id) {}

            public function uniqueId(): string
            {
                return $this->id;
            }

            public function runLock(): void
            {
                $this->onRunHasLock();
            }

            public function endLock(): void
            {
                $this->onEndedHasLock();
            }

            public function getLock()
            {
                return $this->lock;
            }
        };
    }

    public function test_lock_is_acquired_on_run()
    {
        $instance = $this->createHasLockInstance();

        $instance->runLock();

        $this->assertNotNull($instance->getLock());
    }

    public function test_lock_is_released_on_ended()
    {
        $instance = $this->createHasLockInstance();

        $instance->runLock();
        $instance->endLock();

        // A second instance with the same class should be able to acquire the lock
        $instance2 = $this->createHasLockInstance();
        $instance2->runLock();

        $this->assertNotNull($instance2->getLock());
    }

    public function test_lock_is_released_on_activity_fail()
    {
        $instance = $this->createHasLockInstance();

        $instance->runLock();
        $instance->failLock();

        // A second instance with the same class should be able to acquire the lock
        $instance2 = $this->createHasLockInstance();
        $instance2->runLock();

        $this->assertNotNull($instance2->getLock());
    }

    public function test_lock_throws_exception_when_already_acquired()
    {
        $instance = $this->createHasLockInstance();
        $instance->runLock();

        $this->expectException(SyncWorkflowLockException::class);
        $this->expectExceptionMessage('Could not obtain lock');

        $instance2 = $this->createHasLockInstance();
        $instance2->runLock();
    }

    public function test_lock_uses_unique_id_when_available()
    {
        $instance1 = $this->createHasLockInstanceWithUniqueId('workflow-123');
        $instance2 = $this->createHasLockInstanceWithUniqueId('workflow-456');

        // Different unique IDs should not conflict
        $instance1->runLock();
        $instance2->runLock();

        $this->assertNotNull($instance1->getLock());
        $this->assertNotNull($instance2->getLock());
    }

    public function test_lock_with_same_unique_id_throws_exception()
    {
        $instance1 = $this->createHasLockInstanceWithUniqueId('same-id');
        $instance1->runLock();

        $this->expectException(SyncWorkflowLockException::class);

        $instance2 = $this->createHasLockInstanceWithUniqueId('same-id');
        $instance2->runLock();
    }

    public function test_ended_without_lock_does_not_throw()
    {
        $instance = $this->createHasLockInstance();

        // Should not throw when lock is null
        $instance->endLock();

        $this->assertNull($instance->getLock());
    }

    public function test_activity_fail_without_lock_does_not_throw()
    {
        $instance = $this->createHasLockInstance();

        // Should not throw when lock is null
        $instance->failLock();

        $this->assertNull($instance->getLock());
    }
}
