<?php

namespace DTL\Spryker\Fixtures\Loader;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use DTL\Spryker\Fixtures\ClassUtils;
use DTL\Spryker\Fixtures\Loader\EntityRegistry;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;

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
            $logger->loadedClassFqn($classFqn);
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

            $tableMap = Propel::getDatabaseMap()->getTableByPhpName(get_class($entity));

            $this->loadProperties($tableMap, $idRegistry, $entity, $fixture);

            $entity->save();

            $primaryKeys = $tableMap->getPrimaryKeys();

            if (count($primaryKeys) != 1) {
            //    throw new \RuntimeException(sprintf(
            //        'Class with multiple or zero primary keys "%s" not supported (has "%s")',
            //        $classFqn, implode('", "', array_keys($primaryKeys))
            //    ));
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
            $accessor = null;
            if (0 === strpos($value, '@') && false !== strpos($value, ':')) {
                $accessor = substr($value, strpos($value, ':') + 1);
                $value = substr($value, 0, strpos($value, ':'));
            }

            $column = $tableMap->getColumnByPhpName(ucfirst($propertyPath));

            if ($column->isForeignKey()) {
                $fixtureName = $this->fixtureNameFromValue($value);
                $relation = $column->getRelation();
                $value = $idRegistry->entity(
                    $fixtureName
                );

                if ($accessor) {
                    $value = $this->propertyAccessor->getValue($value, $accessor);
                }
            }

            $this->propertyAccessor->setValue($entity, $propertyPath, $value);
        }
    }

    private function fixtureNameFromValue(string $value)
    {
        if (substr($value, 0, 1) !== '@') {
            throw new \RuntimeException(sprintf(
                'Fixture reference must be prefixed with "@" got "%s"', $value

            ));
        }

        return substr($value, 1);
    }
}

