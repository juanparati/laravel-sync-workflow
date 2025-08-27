<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Contracts;

interface Workflow extends SyncExecutorInjector {

    public function runChainedActivities(array $activities, mixed $mainParam = null): mixed;

    public function runActivity(
        string|\Closure $activityClass,
        array $params = [],
        bool $stopOnFail = true,
        ?\Closure $onFail = null
    ): mixed;
}
