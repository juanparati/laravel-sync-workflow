<?php

namespace Juanparati\SyncWorkflow\Casts\SmartSerializationCast;

class SmartSerializeForException
{
    protected array $exception = [];

    public function __construct(\Exception $exception)
    {
        $this->exception = [
            'class'   => get_class($exception),
            'trace'   => $exception->getTraceAsString(),
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
        ];
    }

    public function __serialize(): array
    {
        return $this->exception;
    }

    public function __unserialize(array $data)
    {
        $this->exception = $data;
    }

    public function getRepresentation(): array
    {
        return $this->exception;
    }

}
