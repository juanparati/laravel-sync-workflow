<?php
declare(strict_types=1);

namespace Juanparati\SyncWorkflow;

final class SyncChainedActivity
{
    /**
     * String that represents
     */
    public const string UNDEFINED_PARAM_VALUE = '@___undefined___';

    /**
     * Chained Activity.
     */
    public function __construct(
        protected string|\Closure $activity,
        protected mixed $staticParam = self::UNDEFINED_PARAM_VALUE,
        protected bool $stopOnFail = true,
        protected ?\Closure $onFail = null,
        protected \Closure|bool $when = true,
    ) {}

    public function getActivity(): string
    {
        return $this->activity;
    }

    public function setStaticParam(): void
    {
        $this->staticParam = func_get_args();
    }

    public function getStaticParam(): mixed
    {
        return $this->staticParam;
    }

    public function setOnFail(\Closure $onFail): void
    {
        $this->onFail = $onFail;
    }

    public function getOnFail(): ?\Closure
    {
        return $this->onFail;
    }

    public function setStopOnFail(bool $stopOnFail): void
    {
        $this->stopOnFail = $stopOnFail;
    }

    public function getStopOnFail(): bool
    {
        return $this->stopOnFail;
    }

    public function setWhen(\Closure|bool $when): void
    {
        $this->when = $when;
    }

    public function getWhen(): \Closure|bool
    {
        return $this->when;
    }
}
