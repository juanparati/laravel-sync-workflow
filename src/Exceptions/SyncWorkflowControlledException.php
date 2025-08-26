<?php

declare(strict_types=1);

namespace Juanparati\SyncWorkflow\Exceptions;

/**
 * Controlled exception for workflow.
 */
class SyncWorkflowControlledException extends \Exception
{
    protected array $errors = [];

    public function __construct(string $message = '', int $code = 0, array $errors = [])
    {
        parent::__construct($message, $code);
        $this->setErrors($errors);
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): static
    {
        $this->errors[] = $error;

        return $this;
    }
}
