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
     */
    final public function executor(?SyncExecutor $executor = null): SyncExecutor
    {
        if ($executor) {
            $this->_executor = $executor;
        }

        return $this->_executor;
    }


    /**
     * Wrapper for runChainedActivities.
     *
     * @param array $activities
     * @param mixed|null $mainParam
     * @return mixed
     * @throws \ReflectionException
     * @throws \Throwable
     */
    final public function runChainedActivities(array $activities, mixed $mainParam = null): mixed
    {
        return $this->executor()->runChainedActivities($activities, $mainParam);
    }


    /**
     * Wrapper for runActivity.
     *
     * @param string|\Closure $activityClass
     * @param array $params
     * @param bool $stopOnFail
     * @param \Closure|null $onFail
     * @return mixed
     * @throws \ReflectionException
     * @throws \Throwable
     */
    final public function runActivity(
        string|\Closure $activityClass,
        array $params = [],
        bool $stopOnFail = true,
        ?\Closure $onFail = null
    ): mixed
    {
        return $this->executor()->runActivity($activityClass, $params, $stopOnFail, $onFail);
    }

}
