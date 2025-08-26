<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow;

use Carbon\CarbonInterface;
use Juanparati\SyncWorkflow\Concerns\HasEventSourcing;
use Juanparati\SyncWorkflow\Contracts\WithEventSourcing;
use Juanparati\SyncWorkflow\Contracts\Workflow;
use Juanparati\SyncWorkflow\Exceptions\SyncWorkflowControlledException;
use Ramsey\Uuid\Nonstandard\Uuid;

final class SyncExecutor
{
    use HasEventSourcing;

    /**
     * Workflow Id.
     */
    protected string $workflowId;

    /**
     * Workflow instance.
     */
    protected ?Workflow $workflow = null;

    /**
     * Activity results.
     */
    protected array $activityResults = [];

    /**
     * Final result.
     */
    protected mixed $finalResult = null;

    /**
     * Start time.
     */
    protected ?CarbonInterface $startedAt = null;

    /**
     * End time.
     */
    protected ?CarbonInterface $endedAt = null;

    /**
     * Features used by the workflow.
     */
    protected array $features = [];

    /**
     * Constructor.
     */
    public function __construct(?string $workflowId)
    {
        $this->workflowId = $workflowId ?: Uuid::uuid7()->toString();
    }

    /**
     * Factory method.
     *
     * @return $this
     */
    public static function make(?string $workflowId = null): self
    {
        return new self($workflowId);
    }

    public function load(Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * Start a workflow.
     *
     * @return $this
     */
    public function start(): SyncExecutor
    {
        if (! $this->workflow) {
            throw new \RuntimeException('Workflow not defined, use the load() method before calling start().');
        }

        if ($this->workflow instanceof WithEventSourcing) {
            $this->features[] = 'HasEventSourcing';
        }

        $this->startedAt = now()->toImmutable();
        $this->callHook('onStart');
        $this->workflow->executor($this);
        $this->finalResult = $this->workflow->handle();
        $this->endedAt = now()->toImmutable();
        $this->callHook('onEnded');

        return $this;
    }

    /**
     * Get workflow Id.
     */
    public function getId(): string
    {
        return $this->workflowId;
    }

    /**
     * Get a final workflow result.
     */
    public function getResult(): mixed
    {
        return $this->finalResult;
    }

    /**
     * Get activity results.
     */
    protected function getActivityResults(): array
    {
        return $this->activityResults;
    }

    /**
     * Return the execution time
     *
     * @return array{startedAt: CarbonInterface, endedAt: CarbonInterface, durationTime: float}
     */
    public function getExecutionTime(): array
    {
        return [
            'startedAt' => $this->startedAt,
            'endedAt' => $this->endedAt,
            'durationTime' => $this->startedAt->diffInUTCMicros($this->endedAt),
        ];
    }

    /**
     * Run activity.
     *
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function runActivity(
        string|\Closure $activityClass,
        array $params = [],
        bool $stopOnFail = true,
        ?\Closure $onFail = null
    ): mixed {
        $exception = null;

        $activityResult = [
            'id' => Uuid::uuid7()->toString(),
            'params' => $params,
            'started_at' => now()->getTimestampMs(),
            'finished_at' => null,
            'result' => null,
            'backtrace' => null,
            'sequence' => count($this->activityResults),
        ];

        if (is_callable($activityClass)) {
            $closureResult = null;
            $activityResult['activity'] = 'Closure #'.$activityResult['sequence'];

            try {
                $closureResult = $activityClass(...$params);
            } catch (SyncWorkflowControlledException $e) {
                $closureResult = $e;
            } catch (\Throwable $e) {
                $exception = $e;
                $activityResult['backtrace'] = $e->getTrace();
            }

            $activityResult['result'] = $closureResult;
        } else {
            $capturedArgs = self::captureConstructorArgs($activityClass, ...$params);
            /**
             * @var SyncActivity $activity
             */
            $activity = new $activityClass(...$params);
            $activity->executor($this);
            $activityResult['activity'] = get_class($activity);
            $activityResult['params'] = $capturedArgs;

            try {
                $activityResult['result'] = $activity->handle();
            } catch (SyncWorkflowControlledException $e) {
                $activityResult['result'] = $e;
            } catch (\Throwable $e) {
                $exception = $e;
                $activityResult['backtrace'] = $e->getTrace();
            }
        }

        $activityResult['finished_at'] = now()->getTimestampMs();
        $this->activityResults[] = $activityResult;
        $this->callHook('onRegisterActivity', $activityResult);

        if ($exception) {
            if ($stopOnFail) {

                if ($onFail) {
                    $onFail($exception, $activityResult);
                }

                $this->endedAt = now();

                $this->callHook('onRunActivityFail');
                throw $exception;
            }
        }

        return $activityResult['result'];
    }

    /**
     * Run multiple chained activities.
     *
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function runChainedActivities(
        array $activities,
        mixed $mainParam = null,
    ): mixed {
        foreach ($activities as $activity) {

            if ($activity instanceof SyncChainedActivity) {

                $when = $activity->getWhen();

                if (is_callable($when)) {
                    $when = $when($mainParam);
                }

                if ($when === false) {
                    continue;
                }

                $mainParam = $this->runActivity(
                    $activity->getActivity(),
                    $activity->getStaticParam() === SyncChainedActivity::UNDEFINED_PARAM_VALUE ? [$mainParam] : array_merge([$mainParam], [$activity->getStaticParam()]),
                    $activity->getStopOnFail(),
                    $activity->getOnFail()
                );
            } else {
                $mainParam = $this->runActivity(
                    $activity,
                    [$mainParam],
                );
            }
        }

        return $mainParam;
    }

    /**
     * Capture the constructor arguments.
     *
     * @throws \ReflectionException
     */
    public static function captureConstructorArgs(string $className, ...$args): array
    {
        $reflector = new \ReflectionClass($className);

        // Ensure the class has a constructor
        $constructor = $reflector->getConstructor();
        if (! $constructor) {
            throw new \Exception("No constructor found for class $className");
        }

        // Get the parameters of the constructor
        $parameters = $constructor->getParameters();
        $capturedArgs = [];

        // Map the parameters to the passed arguments
        foreach ($parameters as $index => $parameter) {
            $name = $parameter->getName();
            $capturedArgs[$name] = $args[$index] ?? null; // Capture value or null if not provided
        }

        return $capturedArgs;
    }

    /**
     * Call trait hook.
     */
    private function callHook(string $method, mixed ...$params): void
    {
        foreach ($this->features as $feature) {
            $methodName = $method.ucfirst($feature);
            $this->{$methodName}($params);
        }
    }
}
