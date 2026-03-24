<?php

namespace Juanparati\SyncWorkflow\Test\Unit;

use Illuminate\Support\Facades\File;
use Juanparati\SyncWorkflow\Test\SyncWorkflowTestBase;

class MakeActivityCommandTest extends SyncWorkflowTestBase
{
    protected function tearDown(): void
    {
        // Clean up generated files
        File::deleteDirectory(app_path('SyncWorkflows'));
        File::deleteDirectory(app_path('CustomDir'));

        parent::tearDown();
    }

    public function test_it_creates_an_activity_file()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity'])
            ->expectsOutput('Activity created successfully.')
            ->assertExitCode(0);

        $this->assertFileExists(app_path('SyncWorkflows/MyActivity.php'));
    }

    public function test_generated_activity_has_correct_namespace()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity']);

        $content = File::get(app_path('SyncWorkflows/MyActivity.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows;', $content);
    }

    public function test_generated_activity_has_correct_class_name()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity']);

        $content = File::get(app_path('SyncWorkflows/MyActivity.php'));

        $this->assertStringContainsString('class MyActivity extends SyncActivity', $content);
    }

    public function test_generated_activity_extends_sync_activity()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity']);

        $content = File::get(app_path('SyncWorkflows/MyActivity.php'));

        $this->assertStringContainsString('use Juanparati\SyncWorkflow\SyncActivity;', $content);
        $this->assertStringContainsString('extends SyncActivity', $content);
    }

    public function test_generated_activity_with_custom_directory()
    {
        $this->artisan('make:sync-workflow-activity', [
            'activity' => 'MyActivity',
            '--workflow-dir' => 'CustomDir',
        ]);

        $this->assertFileExists(app_path('CustomDir/MyActivity.php'));

        $content = File::get(app_path('CustomDir/MyActivity.php'));

        $this->assertStringContainsString('namespace App\CustomDir;', $content);
    }

    public function test_generated_activity_with_nested_name()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyWorkflow/MyActivity']);

        $this->assertFileExists(app_path('SyncWorkflows/MyWorkflow/MyActivity.php'));

        $content = File::get(app_path('SyncWorkflows/MyWorkflow/MyActivity.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows\MyWorkflow;', $content);
        $this->assertStringContainsString('class MyActivity extends SyncActivity', $content);
    }

    public function test_generated_activity_with_deeply_nested_name()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'Workflows/Orders/ProcessPaymentActivity']);

        $this->assertFileExists(app_path('SyncWorkflows/Workflows/Orders/ProcessPaymentActivity.php'));

        $content = File::get(app_path('SyncWorkflows/Workflows/Orders/ProcessPaymentActivity.php'));

        $this->assertStringContainsString('namespace App\SyncWorkflows\Workflows\Orders;', $content);
        $this->assertStringContainsString('class ProcessPaymentActivity extends SyncActivity', $content);
    }

    public function test_generated_activity_contains_handle_method()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity']);

        $content = File::get(app_path('SyncWorkflows/MyActivity.php'));

        $this->assertStringContainsString('public function handle()', $content);
    }

    public function test_generated_activity_is_valid_php()
    {
        $this->artisan('make:sync-workflow-activity', ['activity' => 'MyActivity']);

        $path = app_path('SyncWorkflows/MyActivity.php');
        $result = exec("php -l {$path} 2>&1", $output, $exitCode);

        $this->assertEquals(0, $exitCode, 'Generated activity file has PHP syntax errors: ' . implode("\n", $output));
    }
}
