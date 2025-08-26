<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncChainedActivity;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class SyncChainedActivityTest extends SyncWorkflowTestBase
{
    public function test_create_chained_activity_with_default_values()
    {
        $activity = new SyncChainedActivity('TestActivity');

        $this->assertEquals('TestActivity', $activity->getActivity());
        $this->assertEquals(SyncChainedActivity::UNDEFINED_PARAM_VALUE, $activity->getStaticParam());
        $this->assertTrue($activity->getStopOnFail());
        $this->assertNull($activity->getOnFail());
        $this->assertTrue($activity->getWhen());
    }

    public function test_create_chained_activity_with_custom_values()
    {
        $onFail = function () {
            return 'failed';
        };
        $when = function ($param) {
            return $param > 0;
        };

        $activity = new SyncChainedActivity(
            'TestActivity',
            'static-param',
            false,
            $onFail,
            $when
        );

        $this->assertEquals('TestActivity', $activity->getActivity());
        $this->assertEquals('static-param', $activity->getStaticParam());
        $this->assertFalse($activity->getStopOnFail());
        $this->assertSame($onFail, $activity->getOnFail());
        $this->assertSame($when, $activity->getWhen());
    }

    public function test_when_condition_can_be_callable()
    {
        $when = function ($param) {
            return $param === 'expected';
        };
        $activity = new SyncChainedActivity('TestActivity', null, true, null, $when);

        $this->assertIsCallable($activity->getWhen());
    }

    public function test_when_condition_can_be_boolean()
    {
        $activity = new SyncChainedActivity('TestActivity', null, true, null, false);

        $this->assertFalse($activity->getWhen());
    }

    public function test_getters_return_correct_values()
    {
        $onFailCallback = function () {
            return 'error';
        };
        $whenCallback = function ($x) {
            return $x > 5;
        };

        $activity = new SyncChainedActivity(
            'MyActivity',
            ['param1', 'param2'],
            false,
            $onFailCallback,
            $whenCallback
        );

        $this->assertEquals('MyActivity', $activity->getActivity());
        $this->assertEquals(['param1', 'param2'], $activity->getStaticParam());
        $this->assertFalse($activity->getStopOnFail());
        $this->assertSame($onFailCallback, $activity->getOnFail());
        $this->assertSame($whenCallback, $activity->getWhen());
    }
}
