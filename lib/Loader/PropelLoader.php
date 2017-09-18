<?php

namespace DTL\Spryker\Fixtures\Loader;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use DTL\Spryker\Fixtures\ClassUtils;
use DTL\Spryker\Fixtures\Loader\EntityRegistry;
use Propel\Runtime\Map\TableMap;

class PropelLoader
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    public function load(ProgressLogger $logger, array $fixtureSet)
    {
        $idRegistry = new EntityRegistry();
        foreach ($fixtureSet as $classFqn => $fixtures) {
            $classFqn = ClassUtils::normalize($classFqn);
            $logger->loadingClassFqn($classFqn);
            $this->loadFixtures($idRegistry, $logger, $classFqn, $fixtures);
        }
    }

    private function loadFixtures(EntityRegistry $idRegistry, ProgressLogger $logger, string $classFqn, array $fixtures)
    {
        foreach ($fixtures as $name => $fixture) {
            if (!class_exists($classFqn)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown class "%s"', $classFqn
                ));
            }

            /** @var $entity ActiveRecordInterface */
            $entity = new $classFqn();

            $tableMapFqn = constant(get_class($entity) . '::TABLE_MAP');
            /** @var $tableMap \Propel\Runtime\Map\TableMap */
            $tableMap = new $tableMapFqn;

            $this->loadProperties($tableMap, $idRegistry, $entity, $fixture);

            $entity->save();

            $primaryKeys = $tableMap->getPrimaryKeys();

            if (count($primaryKeys) != 1) {
                throw new \RuntimeException(sprintf(
                    'Class with multiple or zero primary keys "%s" not supported (has "%s")',
                    $classFqn, implode('", "', array_keys($primaryKeys))
                ));
            }

            $primaryKey = reset($primaryKeys);
            $getter = 'get' . $primaryKey->getPhpName();

            $idRegistry->register($name, $entity);
            $logger->loadingFixture($name);
        }
    }

    private function loadProperties(TableMap $tableMap, EntityRegistry $idRegistry, ActiveRecordInterface $entity, array $fixture)
    {
        foreach ($fixture as $propertyPath => $value) {
            $column = $tableMap->hasRelation($propertyPath);
            $this->propertyAccessor->setValue($entity, $propertyPath, $value);
        }
    }
}

