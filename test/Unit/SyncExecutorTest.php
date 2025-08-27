<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Concerns\NeedCallProtectedMethods;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;
use Ramsey\Uuid\Uuid;

class SyncExecutorTest extends SyncWorkflowTestBase
{

    use NeedCallProtectedMethods;

    public function test_make_creates_instance_with_auto_generated_id()
    {
        $executor = SyncExecutor::make();

        $this->assertInstanceOf(SyncExecutor::class, $executor);
        $this->assertTrue(Uuid::isValid($executor->getId()));
    }

    public function test_make_creates_instance_with_provided_id()
    {
        $customId = 'custom-workflow-id';
        $executor = SyncExecutor::make($customId);

        $this->assertEquals($customId, $executor->getId());
    }

    public function test_start_without_workflow_throws_exception()
    {
        $executor = SyncExecutor::make();

        $this->expectException(\RuntimeException::class);
        $executor->run();
    }

    public function test_capture_constructor_args_maps_parameters_correctly()
    {
        $args = $this->makeAvailableProtectedStaticMethod(
            SyncExecutor::class,
            'captureConstructorArgs'
        )
            ->invokeArgs(null, [
                TestClassWithConstructor::class,
                'param1',
                42,
                true
            ]);

        $expected = [
            'name' => 'param1',
            'age' => 42,
            'active' => true,
        ];

        $this->assertEquals($expected, $args);
    }

    public function test_capture_constructor_args_handles_missing_parameters()
    {

        $args = $this->makeAvailableProtectedStaticMethod(
            SyncExecutor::class,
            'captureConstructorArgs'
        )
            ->invokeArgs(null, [
                TestClassWithConstructor::class,
                'param1',
            ]);

        $expected = [
            'name' => 'param1',
            'age' => null,
            'active' => null,
        ];

        $this->assertEquals($expected, $args);
    }
}

class TestClassWithConstructor
{
    public function __construct(
        public string $name,
        public ?int $age = null,
        public ?bool $active = null
    ) {}
}

class TestClassWithoutConstructor
{
    // No constructor
}
