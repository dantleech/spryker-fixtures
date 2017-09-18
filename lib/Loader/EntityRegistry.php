<?php

namespace DTL\Spryker\Fixtures\Loader;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

class EntityRegistry
{
    private $entityMap = [];

    public function register($name, ActiveRecordInterface $entity)
    {
        $classFqn = get_class($entity);

        if (!isset($this->entityMap[$classFqn])) {
            $this->entityMap[$classFqn] = [];
        }

        $this->entityMap[$classFqn][$name] = $entity;
    }

    public function id(string $classFqn, string $name)
    {
        if (false === isset($this->entityMap[$classFqn])) {
            throw new \RuntimeException(sprintf(
                'No fixtures for class "%s" have been persisted (while trying to get "%s"), yet. Registered classes "%s"',
                $classFqn, $name, implode('", "', array_keys($this->entityMap))
            ));
        }

        if (false === isset($this->entityMap[$classFqn][$name])) {
            throw new \RuntimeException(sprintf(
                'No fixture "%s" has been persisted yet, persisted fixtures for "%s": "%s"',
                $name, $classFqn, implode('", "', array_keys($this->entityMap[$classFqn]))
            ));
        }

        return $this->entityMap[$classFqn][$name];
    }
}
