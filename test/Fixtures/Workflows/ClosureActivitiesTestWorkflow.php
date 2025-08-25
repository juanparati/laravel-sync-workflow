<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;


use Juanparati\SyncWorkflow\SyncWorkflow;

class ClosureActivitiesTestWorkflow extends SyncWorkflow
{
    public function __construct(protected string $myParam) {}

    public function handle()
    {
        return $this->executor()
            ->runChainedActivities(
                [
                    fn() => 'st' . $this->myParam,
                    fn($val) => 'te' . $val,
                ],
                $this->myParam
            );
    }
}
