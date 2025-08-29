<?php

namespace Juanparati\SyncWorkflow\Console\Commands;

use Illuminate\Console\Command;
use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowControlledException;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;
use Juanparati\SyncWorkflow\SyncExecutor;
use Juanparati\SyncWorkflow\Test\Fixtures\Workflows\GenericActivities\ControlledExceptionActivity;

class SyncWorkflowReplayCommand extends Command
{
    protected $signature = 'sync-workflow:replay {id}
        {--force : Force replay workflows even if they were successful executed}
    ';

    protected $description = 'Replay workflow';

    public function handle(): int
    {
        if (!($state = SyncWorkflowState::find($this->argument('id')))) {
            $this->error('Workflow state not found.');
            return -1;
        }

        if ($state->was_success && !$this->option('force')) {
            $this->error('Workflow was already successfully executed.');
            return -1;
        }

        $executor = SyncExecutor::make($this->argument('id'))->load($state->instance);

        $this->info("Loaded workflow {$executor->getId()}");
        $this->info('Running workflow...');

        try {
            $executor->run();
        } catch (SyncWorkflowControlledException $e) {
            $this->warn('Controlled exception:' . $e->getMessage());
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return -1;
        }

        $this->output->success('Workflow finished successfully.');

        return 0;
    }
}
