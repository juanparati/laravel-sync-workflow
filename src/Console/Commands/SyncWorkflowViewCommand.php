<?php

namespace Juanparati\SyncWorkflow\Console\Commands;

use Illuminate\Console\Command;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;

class SyncWorkflowViewCommand extends Command
{
    protected $signature = 'sync-workflow:view {id}';

    protected $description = 'View workflow state';

    public function handle(): int
    {
        if (!($state = SyncWorkflowState::find($this->argument('id')))) {
            $this->error('Workflow state not found.');
            return -1;
        }

        $state = $state->toArray();

        $this->table(
            array_keys($state),
            array_values($state)
        );

        return 0;
    }
}
