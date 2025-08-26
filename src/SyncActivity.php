<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow;

use Juanparati\SyncWorkflow\Contracts\Activity;

abstract class SyncActivity implements Activity
{
    /**
     * Parent workflow.
     */
    private ?SyncExecutor $_executor = null;

    /**
     * Get/Set Executor.
     */
    final public function executor(?SyncExecutor $executor = null): SyncExecutor
    {
        if ($executor) {
            $this->_executor = $executor;
        }

        return $this->_executor;
    }
}
