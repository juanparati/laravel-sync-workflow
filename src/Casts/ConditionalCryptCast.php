<?php

namespace Juanparati\SyncWorkflow\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ConditionalCryptCast implements CastsAttributes
{
    const string CRYPT_PREFIX = '_CRYPT:';

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $val = str($value);

        if ($val->startsWith(static::CRYPT_PREFIX)) {
            return decrypt($val->after(static::CRYPT_PREFIX)->toString());
        }

        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (config('sync-workflow.encrypt')) {
            return static::CRYPT_PREFIX.encrypt($value);
        }

        return $value;
    }
}
