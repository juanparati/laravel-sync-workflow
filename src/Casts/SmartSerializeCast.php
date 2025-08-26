<?php

namespace Juanparati\SyncWorkflow\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Juanparati\SyncWorkflow\Casts\SmartSerializationCast\SmartSerializeForException;
use Juanparati\SyncWorkflow\Casts\SmartSerializationCast\SmartSerializeForModel;

class SmartSerializeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return $this->decodeAll(
            unserialize((new ConditionalCryptCast)->get($model, $key, $value, $attributes))
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return (new ConditionalCryptCast)->set(
            $model, $key, serialize($this->encodeAll($value)), $attributes
        );
    }

    protected function encodeAll($value)
    {
        if (is_array($value)) {
            $value = array_map([$this, 'encodeAll'], $value);
        }

        if ($value instanceof Model) {
            $value = new SmartSerializeForModel($value);
        }

        if ($value instanceof \Exception) {
            $value = new SmartSerializeForException($value);
        }

        return $value;
    }

    protected function decodeAll($value)
    {
        if (is_array($value)) {
            $value = array_map([$this, 'decodeAll'], $value);
        }

        if ($value instanceof SmartSerializeForModel) {
            $value = $value->getInstance();
        }

        if ($value instanceof SmartSerializeForException) {
            $value = $value->getRepresentation();
        }

        return $value;
    }
}
