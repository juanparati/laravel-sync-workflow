<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities;

use Juanparati\SyncWorkflow\SyncActivity;

class TimeActivity extends SyncActivity
{
    public function __construct() {}

    public function handle()
    {
        return $this->executor()->relativeNow();
    }
}
