<?php
declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Concerns;

use Carbon\CarbonInterface;
use Juanparati\SyncWorkflow\Models\SyncWorkflowState;

/**
 * Provide the ability to keep track of the workflow state and to replay it.
 */
trait HasEventSourcing
{
    private ?SyncWorkflowState $workflowState = null;

    /**
     * Runs when the workflow starts.
     */
    protected function onStartHasEventSourcing(): void
    {
        $this->workflowState = SyncWorkflowState::findOrNew($this->workflowId);
        $this->workflowState->first_started_at = $this->workflowState->first_started_at ?: $this->startedAt;
        $this->workflowState->started_at = $this->startedAt;
        $this->workflowState->workflow = get_class($this->workflow);
        $this->workflowState->instance = $this->workflow;
        $this->workflowState->save();
    }

    /**
     * Runs when the workflow ends.
     */
    protected function onEndedHasEventSourcing(): void
    {
        $this->workflowState->update([
            'result' => $this->finalResult,
            'was_success' => true,
            'finished_at' => $this->endedAt,
        ]);
    }

    /**
     * Run when a new activity is registered.
     */
    protected function onRegisterActivityHasEventSourcing(array $activityResult): void
    {
        $activities = $this->workflowState->activities;
        $activities[] = $activityResult;
        $this->workflowState->activities = $activities;
        $this->workflowState->save();
    }

    /**
     * Runs when activity execution failed.
     */
    protected function onRunActivityFailHasEventSourcing(): void
    {
        $this->workflowState
            ->update([
                'result' => null,
                'was_success' => false,
                'finished_at' => $this->endedAt,
            ]);
    }

    /**
     * Get the relative time.
     */
    public function relativeNow(): CarbonInterface
    {
        $relativeTime = $this->startedAt;

        if ($this->workflowState) {
            $relativeTime =  $this->startedAt->addMicroseconds(
                now()->diffInMicroseconds($this->workflowState->first_started_at)
            );
        }

        return $relativeTime
            ->copy()
            ->toMutable();
    }
}
