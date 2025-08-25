<?php

namespace Juanparati\SyncWorkflow\Console\Commands;

use Illuminate\Console\Command;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;

class SyncWorkflowViewCommand extends Command
{
    protected $signature = 'sync-workflow:view {id}';

    protected $description = 'View workflow state';

    public function handle(): void
    {
        $state = SyncWorkflowState::findOrFail($this->argument('id'))->toArray();

        $this->table(
            array_keys($state),
            array_values($state)
        );
    }
}
