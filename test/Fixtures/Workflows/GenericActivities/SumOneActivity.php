<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities;

use Juanparati\SyncWorkflow\SyncActivity;

class SumOneActivity extends SyncActivity
{
    public function __construct(protected int|float $myParam) {}

    public function handle()
    {
        return $this->myParam + 1;
    }
}
