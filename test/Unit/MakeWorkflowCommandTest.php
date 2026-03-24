<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Support\Facades\File;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class MakeWorkflowCommandTest extends SyncWorkflowTestBase
{
    protected function tearDown(): void
    {
        // Clean up generated files
        File::deleteDirectory(app_path('SyncWorkflows'));
        File::deleteDirectory(app_path('CustomDir'));

        parent::tearDown();
    }

    public function test_it_creates_a_workflow_file()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow'])
            ->expectsOutput('Workflow created successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('SyncWorkflows/MyWorkflow.php'));
    }

    public function test_generated_workflow_has_correct_namespace()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows;', $content);
    }

    public function test_generated_workflow_has_correct_class_name()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringContainsString('class MyWorkflow extends SyncWorkflow', $content);
    }

    public function test_generated_workflow_extends_sync_workflow()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringContainsString('use Juanparati\SyncWorkflow\SyncWorkflow;', $content);
        $this->assertStringContainsString('extends SyncWorkflow', $content);
    }

    public function test_generated_workflow_without_event_sourcing()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringNotContainsString('implements WithEventSourcing', $content);
    }

    public function test_generated_workflow_with_event_sourcing()
    {
        $this->artisan('make:sync-workflow', [
            'workflow' => 'MyWorkflow',
            '--event-sourcing' => true,
        ]);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringContainsString('implements WithEventSourcing', $content);
        $this->assertStringContainsString('use Juanparati\SyncWorkflow\Contracts\WithEventSourcing;', $content);
    }

    public function test_generated_workflow_with_custom_directory()
    {
        $this->artisan('make:sync-workflow', [
            'workflow' => 'MyWorkflow',
            '--workflow-dir' => 'CustomDir',
        ]);

        $this->assertFileExists(app_path('CustomDir/MyWorkflow.php'));

        $content = File::get(app_path('CustomDir/MyWorkflow.php'));

        $this->assertStringContainsString('namespace App\CustomDir;', $content);
    }

    public function test_generated_workflow_with_nested_name()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'SubDir/MyWorkflow']);

        $this->assertFileExists(app_path('SyncWorkflows/SubDir/MyWorkflow.php'));

        $content = File::get(app_path('SyncWorkflows/SubDir/MyWorkflow.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows\SubDir;', $content);
        $this->assertStringContainsString('class MyWorkflow extends SyncWorkflow', $content);
    }

    public function test_generated_workflow_with_deeply_nested_name()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'Level1/Level2/MyWorkflow']);

        $this->assertFileExists(app_path('SyncWorkflows/Level1/Level2/MyWorkflow.php'));

        $content = File::get(app_path('SyncWorkflows/Level1/Level2/MyWorkflow.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows\Level1\Level2;', $content);
        $this->assertStringContainsString('class MyWorkflow extends SyncWorkflow', $content);
    }

    public function test_generated_workflow_contains_handle_method()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $content = File::get(app_path('SyncWorkflows/MyWorkflow.php'));

        $this->assertStringContainsString('public function handle()', $content);
    }

    public function test_generated_workflow_is_valid_php()
    {
        $this->artisan('make:sync-workflow', ['workflow' => 'MyWorkflow']);

        $path = app_path('SyncWorkflows/MyWorkflow.php');
        $result = exec("php -l {$path} 2>&1", $output, $exitCode);

        $this->assertEquals(0, $exitCode, 'Generated workflow file has PHP syntax errors: ' . implode("\n", $output));
    }
}
