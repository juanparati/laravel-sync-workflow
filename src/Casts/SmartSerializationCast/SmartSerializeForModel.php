<?php

namespace Juanparati\SyncWorkflow\Casts\SmartSerializationCast;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class SmartSerializeForModel
{
    use SerializesAndRestoresModelIdentifiers;

    protected string $modelClass = '';

    protected array $modelAttributes = [];

    public function __construct(Model $model)
    {
        $this->modelClass = get_class($model);
        $this->modelAttributes = $model->getAttributes();
    }

    /**
     * Prepare the instance values for serialization.
     *
     * @return array
     */
    public function __serialize()
    {
        return [
            'modelClass' => $this->modelClass,
            'modelAttributes' => $this->modelAttributes,
        ];
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __unserialize(array $values)
    {
        $this->modelClass = $values['modelClass'];
        $this->modelAttributes = $values['modelAttributes'];
    }

    /**
     * Return a deserialized model instance.
     */
    public function getInstance(): Model
    {
        $model = $this->modelClass;

        return (new $model)->setRawAttributes($this->modelAttributes);
    }
}
