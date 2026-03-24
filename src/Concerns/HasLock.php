<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Concerns;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowLockException;

/**
 * Provide the ability to lock the workflow.
 */
trait HasLock
{

    /**
     * Default lock time.
     *
     * @var int
     */
    protected int $lockTime = 60;


    protected ?Lock $lock = null;

    /**
     * Runs when the workflow starts.
     */
    protected function onRunHasLock(): void
    {
        if (!method_exists($this, 'uniqueId')) {
            $uniqueId = static::class . ':lock';
        } else {
            $uniqueId = $this->uniqueId();
        }

        $this->lock = Cache::lock($uniqueId, $this->lockTime);

        if (!$this->lock->get()) {
            throw new SyncWorkflowLockException('Could not obtain lock');
        }
    }

    /**
     * Runs when the workflow ends.
     */
    protected function onEndedHasLock(): void
    {
        $this->lock?->release();
    }


    /**
     * Runs when activity execution failed.
     */
    protected function onRunActivityFailHasLock(): void
    {
        $this->lock?->release();
    }
}
