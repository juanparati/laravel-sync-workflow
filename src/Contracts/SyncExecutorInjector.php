<?php
declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Contracts;

use Juanparati\SyncWorkflow\SyncExecutor;

interface SyncExecutorInjector
{
    /**
     * Run.
     */
    public function handle();

    /**
     * Get/Set Executor.
     *
     * @param SyncExecutor|null $executor
     * @return SyncExecutor
     */
    public function executor(?SyncExecutor $executor): SyncExecutor;
}
