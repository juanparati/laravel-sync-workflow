<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncActivity;
use Juanparati\SyncWorkflow\SyncChainedActivity;
use Juanparati\SyncWorkflow\SyncExecutor;
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

    public function test_decoupled_true_clones_object_parameters_during_execution()
    {
        $testObject = new TestParameterObject('original');
        $executor = SyncExecutor::make();

        $chainedActivity = new SyncChainedActivity(
            ObjectModifierActivity::class,
            SyncChainedActivity::UNDEFINED_PARAM_VALUE,
        );

        $result = $executor->runChainedActivities([$chainedActivity], $testObject);

        $this->assertEquals('original', $testObject->value);
        $this->assertEquals('modified', $result->value);
        $this->assertNotSame($testObject, $result);
    }

    public function test_decoupled_false_shares_object_references_during_execution()
    {
        $testObject = new TestParameterObject('original');
        $executor = SyncExecutor::make();

        $chainedActivity = new SyncChainedActivity(
            activity: ObjectModifierActivity::class,
            staticParam: SyncChainedActivity::UNDEFINED_PARAM_VALUE,
            decoupled: false,
        );

        $result = $executor->runChainedActivities([$chainedActivity], $testObject);

        $this->assertEquals('modified', $testObject->value);
        $this->assertEquals('modified', $result->value);
        $this->assertSame($testObject, $result);
    }

    public function test_decoupled_true_with_closure_clones_objects()
    {
        $testObject = new TestParameterObject('original');
        $executor = SyncExecutor::make();

        $chainedActivity = new SyncChainedActivity(
            function ($obj) {
                $obj->value = 'modified by closure';
                return $obj;
            },
            SyncChainedActivity::UNDEFINED_PARAM_VALUE,
        );

        $result = $executor->runChainedActivities([$chainedActivity], $testObject);

        $this->assertEquals('original', $testObject->value);
        $this->assertEquals('modified by closure', $result->value);
    }

    public function test_decoupled_false_with_closure_shares_references()
    {
        $testObject = new TestParameterObject('original');
        $executor = SyncExecutor::make();

        $chainedActivity = new SyncChainedActivity(
            function ($obj) {
                $obj->value = 'modified by closure';
                return $obj;
            },
            SyncChainedActivity::UNDEFINED_PARAM_VALUE,
            true,
            null,
            true,
            false
        );

        $result = $executor->runChainedActivities([$chainedActivity], $testObject);

        $this->assertEquals('modified by closure', $testObject->value);
        $this->assertEquals('modified by closure', $result->value);
        $this->assertSame($testObject, $result);
    }

    public function test_decoupled_with_primitive_parameters_behavior()
    {
        $primitiveValue = 42;
        $executor = SyncExecutor::make();

        $chainedActivityDecoupled = new SyncChainedActivity(
            function ($value) {
                return $value * 2;
            },
            SyncChainedActivity::UNDEFINED_PARAM_VALUE,
            true,
            null,
            true,
            true
        );

        $chainedActivityCoupled = new SyncChainedActivity(
            function ($value) {
                return $value * 2;
            },
            SyncChainedActivity::UNDEFINED_PARAM_VALUE,
            true,
            null,
            true,
            false
        );

        $resultDecoupled = $executor->runChainedActivities([$chainedActivityDecoupled], $primitiveValue);
        $resultCoupled = $executor->runChainedActivities([$chainedActivityCoupled], $primitiveValue);

        $this->assertEquals(42, $primitiveValue);
        $this->assertEquals(84, $resultDecoupled);
        $this->assertEquals(84, $resultCoupled);
    }
}

class TestParameterObject
{
    public function __construct(public string $value)
    {
    }
}

class ObjectModifierActivity extends SyncActivity
{
    public function __construct(public TestParameterObject $obj)
    {
    }

    public function handle(): TestParameterObject
    {
        $this->obj->value = 'modified';
        return $this->obj;
    }
}
