<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Database\Eloquent\Model;
use Juanparati\SyncWorkflow\Casts\ConditionalCryptCast;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class ConditionalCryptCastTest extends SyncWorkflowTestBase
{
    private ConditionalCryptCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new ConditionalCryptCast;
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

    public function test_returns_value_unchanged_when_encryption_disabled()
    {
        config(['sync-workflow' => ['encrypt' => false]]);

        $testValue = 'test-value';
        $encoded = $this->cast->set($this->model, 'test_key', $testValue, []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertEquals($testValue, $decoded);
    }

    public function test_encrypts_and_decrypts_when_encryption_enabled()
    {
        config(['sync-workflow' => ['encrypt' => true]]);

        $testValue = 'sensitive-data';
        $encoded = $this->cast->set($this->model, 'test_key', $testValue, []);

        // Encoded value should be different from original
        $this->assertNotEquals($testValue, $encoded);

        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        // But decoding should return the original value
        $this->assertEquals($testValue, $decoded);
    }

    public function test_handles_complex_data_structures()
    {
        config(['sync-workflow.encrypt_workflow_instance' => true]);

        $complexData = [
            'string' => 'test',
            'number' => 42,
            'array' => ['nested', 'values'],
            'object' => (object) ['property' => 'value'],
        ];

        $encoded = $this->cast->set($this->model, 'test_key', serialize($complexData), []);
        $decoded = $this->cast->get($this->model, 'test_key', $encoded, []);

        $this->assertEquals(serialize($complexData), $decoded);
    }

    public function test_encryption_config_defaults_to_false()
    {
        // Clear any existing config
        config(['sync-workflow.encrypt_workflow_instance' => null]);

        $testValue = 'test-value';
        $encoded = $this->cast->set($this->model, 'test_key', $testValue, []);

        // Should return the value unchanged when config is null/false
        $this->assertEquals($testValue, $encoded);
    }
}
