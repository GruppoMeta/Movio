<?php
trait org_glizy_dataAccessRepository_EntityBuilderTrait
{
    private $reflectionClass;

    private function createEntity($className, $args)
    {
        $className = str_replace('.', '_', $className);

        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception(sprintf('%s: class %s is not instantiable', __METHOD__, $className));
        }

        $rewriteArgs = array();
        $constructor = $reflectionClass->getConstructor();
        if ($constructor) {
            $construcParameters = $constructor->getParameters();

            foreach ($construcParameters as $key => $param) {
                if (isset($args[$key])) {
                    $rewriteArgs[$key] = $args[$key];
                } else {
                    $rewriteArgs[$key] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                }
            }
        }
        $entity = $reflectionClass->newInstanceArgs($rewriteArgs);

        foreach ($entity as $key => $value) {
            if (!isset($rewriteArgs[$key]) && isset($args[$key])) {
                $entity->{$key} = $args[$key];
            }

        }

        return $entity;
    }
}