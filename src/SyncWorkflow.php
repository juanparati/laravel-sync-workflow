<?php
declare(strict_types=1);

namespace Juanparati\SyncWorkflow;

use Illuminate\Queue\SerializesModels;
use Juanparati\SyncWorkflow\Contracts\Workflow;

abstract class SyncWorkflow implements Workflow
{

    use SerializesModels;

    /**
     * Parent workflow.
     */
    private ?SyncExecutor $_executor = null;


    /**
     * Get/Set Executor.
     *
     * @param SyncExecutor|null $executor
     * @return SyncExecutor
     */
    final public function executor(?SyncExecutor $executor = null): SyncExecutor
    {
        if ($executor)
            $this->_executor = $executor;

        return $this->_executor;
    }

}
