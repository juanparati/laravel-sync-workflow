<?php

namespace Juanparati\SyncWorkflow\Test\Concerns;

trait NeedCallProtectedMethods
{
    protected function makeAvailableProtectedStaticMethod($class, $methodName) : \ReflectionMethod
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

}
