<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Juanparati\SyncWorkflow\Casts\ConditionalCryptCast;
use Juanparati\SyncWorkflow\Casts\SmartSerializeCast;
use Juanparati\SyncWorkflow\SyncExecutor;

class SyncWorkflowState extends Model
{
    use HasUuids;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'activities' => SmartSerializeCast::class,
            'result' => SmartSerializeCast::class,
            'instance' => ConditionalCryptCast::class,
            'was_success' => 'boolean',
            'first_started_at' => 'datetime:Y-m-d H:i:s.u',
            'started_at' => 'datetime:Y-m-d H:i:s.u',
            'finished_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('sync-workflow.table_name', 'sync_workflow_states'));
    }

    /**
     * Replay workflow.
     */
    public function replay(): SyncExecutor
    {
        if (! $this->id) {
            throw new \RuntimeException('Workflow state not found.');
        }

        return SyncExecutor::make($this->id)
            ->load($this->instance)
            ->start();
    }

    protected function instance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => unserialize((new ConditionalCryptCast)->get($this, 'instance', $value, $this->attributes)),
            set: fn ($value) => (new ConditionalCryptCast)->set($this, 'instance', serialize($value), $this->attributes),
        );
    }

    /**
     * Force to use milliseconds (cast doesn't work).
     */
    protected function firstStartedAt(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->microTimeAttrFnc()($value)
        );
    }

    /**
     * Force to use milliseconds (cast doesn't work).
     */
    protected function startedAt(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->microTimeAttrFnc()($value)
        );
    }

    /**
     * Force to use milliseconds (cast doesn't work).
     */
    protected function finishedAt(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->microTimeAttrFnc()($value)
        );
    }

    /**
     * Return closure that cast to microtime.
     */
    protected function microTimeAttrFnc(): \Closure
    {
        return fn ($value) => $value ? $value->format('Y-m-d H:i:s.u') : null;
    }
}
