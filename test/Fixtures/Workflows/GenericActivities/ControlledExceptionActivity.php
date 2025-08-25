<?php

namespace Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities;

use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowControlledException;
use Juanparati\SyncWorkflow\SyncActivity;

class ControlledExceptionActivity extends SyncActivity
{
    public function __construct() {}

    public function handle()
    {
        throw (new SyncWorkflowControlledException('test'))->addError('test');
    }
}
