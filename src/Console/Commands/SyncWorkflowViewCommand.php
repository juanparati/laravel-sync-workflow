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
        if (! ($state = SyncWorkflowState::find($this->argument('id')))) {
            $this->error('Workflow state not found.');

            return -1;
        }

        $this->output->title('Workflow state: ' . $state->id);

        $state = $state->toArray();

        foreach ($state as $key => $value) {
            $this->info(str($key)->ucfirst()->append(':'));

            if (is_null($value)) {
                $value = 'NULL';
            }

            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            $this->line($value);
            $this->newLine();
        }


        return 0;
    }
}
