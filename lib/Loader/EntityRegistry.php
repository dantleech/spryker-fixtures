<?php

namespace DTL\Spryker\Fixtures\Loader;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use DTL\Spryker\Fixtures\ClassUtils;

class EntityRegistry
{
    /**
     * @var array
     */
    private $entityMap = [];

    /**
     * @var array
     */
    private $entityIds = [];

    public function register($name, ActiveRecordInterface $entity, int $primaryKey)
    {
        $classFqn = get_class($entity);
        $classFqn = ClassUtils::normalize($classFqn);

        if (isset($this->entityMap[$name])) {
            throw new \RuntimeException(sprintf(
                'An entity has already been registered with name "%s"',
                $name
            ));
        }

        $this->entityMap[$name] = $entity;
        $this->entityIds[$name] = $primaryKey;
    }

    public function entity(string $name)
    {
        if (false === isset($this->entityMap[$name])) {
            throw new \RuntimeException(sprintf(
                'No fixture "%s" has been persisted yet, persisted fixtures "%s"',
                $name, implode('", "', array_keys($this->entityMap))
            ));
        }

        return $this->entityMap[$name];
    }

    public function idMap(): array
    {
        return $this->entityIds;
    }
}
