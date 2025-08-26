<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Database\Eloquent\Model;
use Juanparati\SyncWorkflow\Casts\SmartSerializeCast;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class SmartSerializeCastTest extends SyncWorkflowTestBase
{
    private SmartSerializeCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new SmartSerializeCast;
        $this->model = new class extends Model
        {
            protected $table = 'test_table';
        };
    }

    public function test_get_returns_null_for_null_value()
    {
        $result = $this->cast->get($this->model, 'test_key', null, []);

        $this->assertNull($result);
    }

    public function test_set_returns_null_for_null_value()
    {
        $result = $this->cast->set($this->model, 'test_key', null, []);

        $this->assertNull($result);
    }

    public function test_encodes_and_decodes_simple_array()
    {
        $originalData = ['key' => 'value', 'number' => 42];

        $encoded = $this->cast->set($this->model, 'test_key', $originalData, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertEquals($originalData, $decoded);
    }

    public function test_encodes_model_instances()
    {
        $testModel = new class extends Model
        {
            protected $fillable = ['name'];
        };
        $testModel->fill(['name' => 'Test Model']);

        $data = ['model' => $testModel];
        $encoded = $this->cast->set($this->model, 'test_key', $data, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertInstanceOf(get_class($testModel), $decoded['model']);
        $this->assertEquals('Test Model', $decoded['model']->name);
    }

    public function test_encodes_exception_instances()
    {
        $exception = new \RuntimeException('Test exception', 500);
        $data = ['error' => $exception];

        $encoded = $this->cast->set($this->model, 'test_key', $data, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertIsArray($decoded['error']);
        $this->assertEquals('RuntimeException', $decoded['error']['class']);
        $this->assertEquals('Test exception', $decoded['error']['message']);
        $this->assertEquals(500, $decoded['error']['code']);
    }

    public function test_handles_nested_arrays_with_models_and_exceptions()
    {
        $testModel = new class extends Model
        {
            protected $fillable = ['name'];
        };
        $testModel->fill(['name' => 'Nested Model']);

        $exception = new \InvalidArgumentException('Nested exception');

        $data = [
            'level1' => [
                'level2' => [
                    'model' => $testModel,
                    'exception' => $exception,
                    'simple' => 'value',
                ],
            ],
        ];

        $encoded = $this->cast->set($this->model, 'test_key', $data, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertInstanceOf(get_class($testModel), $decoded['level1']['level2']['model']);
        $this->assertEquals('Nested Model', $decoded['level1']['level2']['model']->name);
        $this->assertIsArray($decoded['level1']['level2']['exception']);
        $this->assertEquals('InvalidArgumentException', $decoded['level1']['level2']['exception']['class']);
        $this->assertEquals('Nested exception', $decoded['level1']['level2']['exception']['message']);
        $this->assertEquals('value', $decoded['level1']['level2']['simple']);
    }

    public function test_preserves_non_model_non_exception_objects()
    {
        $stdObject = (object) ['property' => 'value'];
        $data = ['object' => $stdObject];

        $encoded = $this->cast->set($this->model, 'test_key', $data, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertEquals($stdObject, $decoded['object']);
    }
}
