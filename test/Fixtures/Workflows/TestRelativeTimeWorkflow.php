<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows;

use Juanparati\SyncWorkflow\SyncWorkflow;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\TimeActivity;

class TestRelativeTimeWorkflow extends SyncWorkflow
{
    public function __construct() {}

    public function handle()
    {
        return $this->executor()->runActivity(TimeActivity::class);
    }
}
