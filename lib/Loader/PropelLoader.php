<?php

namespace DTL\Spryker\Fixtures\Loader;

use DTL\Spryker\Fixtures\ClassUtils;
use DTL\Spryker\Fixtures\Loader\EntityRegistry;
use DTL\Spryker\Fixtures\ValueResolver\ConstantResolver;
use DTL\Spryker\Fixtures\ValueResolver\DelegatingResolver;
use DTL\Spryker\Fixtures\ValueResolver\ValueResolver;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * TODO: Reference resolution can be refactored into a value resolver.
 */
class PropelLoader
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->valueResolver = new DelegatingResolver([
            'constant' => new ConstantResolver(),
        ]);
    }

    public function load(ProgressLogger $logger, array $fixtureSet): EntityRegistry
    {
        $entityRegistry = new EntityRegistry();
        foreach ($fixtureSet as $classFqn => $fixtures) {
            $classFqn = ClassUtils::normalize($classFqn);
            $logger->loadingClassFqn($classFqn);
            $this->loadFixtures($entityRegistry, $logger, $classFqn, $fixtures);
            $logger->loadedClassFqn($classFqn);
        }

        return $entityRegistry;
    }

    private function loadFixtures(EntityRegistry $entityRegistry, ProgressLogger $logger, string $classFqn, array $fixtures)
    {
        foreach ($fixtures as $name => $fixture) {
            if (!class_exists($classFqn)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown class "%s"', $classFqn
                ));
            }

            /** @var ActiveRecordInterface $entity */
            $entity = new $classFqn();

            $tableMap = Propel::getDatabaseMap()->getTableByPhpName(get_class($entity));

            $this->loadProperties($tableMap, $entityRegistry, $entity, $fixture);

            $entity->save();

            $primaryKeys = $tableMap->getPrimaryKeys();

            $primaryKey = reset($primaryKeys);
            $getter = 'get' . $primaryKey->getPhpName();

            $entityRegistry->register($name, $entity, $entity->$getter());
            $logger->loadingFixture($name);
        }
    }

    private function loadProperties(TableMap $tableMap, EntityRegistry $entityRegistry, ActiveRecordInterface $entity, array $fixture)
    {
        foreach ($fixture as $propertyPath => $value) {
            $this->propertyAccessor->setValue($entity, $propertyPath, $this->resolveValue($tableMap, $entityRegistry, $propertyPath, $value));
        }
    }

    private function fixtureNameFromValue(string $value)
    {
        if (substr($value, 0, 1) !== '@') {
            throw new RuntimeException(sprintf(
                'Fixture reference must be prefixed with "@" got "%s"', $value

            ));
        }

        return substr($value, 1);
    }

    private function resolveValue(TableMap $tableMap, EntityRegistry $entityRegistry, string $propertyPath, $value)
    {
        if (is_array($value)) {
            return $this->valueResolver->resolveValue($value);
        }

        $accessor = null;
        if (0 === strpos($value, '@') && false !== strpos($value, ':')) {
            $accessor = substr($value, strpos($value, ':') + 1);
            $value = substr($value, 0, strpos($value, ':'));
        }

        $column = $tableMap->getColumnByPhpName(ucfirst($propertyPath));

        if (substr($value, 0, 1) == '@') {
            $fixtureName = $this->fixtureNameFromValue($value);
            $relation = $column->getRelation();
            $value = $entityRegistry->entity(
                $fixtureName
            );

            if ($accessor) {
                $value = $this->propertyAccessor->getValue($value, $accessor);
            }
        }

        return $value;
    }

}
