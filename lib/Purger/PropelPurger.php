<?php

namespace DTL\Spryker\Fixtures\Purger;

use Propel\Runtime\Propel;
use Propel\Runtime\Map\DatabaseMap;
use Propel\Runtime\Map\RelationMap;
use DTL\Spryker\Fixtures\ClassUtils;

class PropelPurger
{
    public function purge(ProgressLogger $logger, array $classFqns)
    {
        // TODO: FK constraints! Build a dependency tree or brute-force (keep
        //       trying until all possiblities are used up)
        foreach ($classFqns as $classFqn) {
            $classFqn = ClassUtils::normalize($classFqn);
            $this->purgeClassFqn($logger, $classFqn);
        }
    }

    private function purgeClassFqn(ProgressLogger $logger, string $classFqn, &$purgedClassFqns = [])
    {
        if (isset($purgedClassFqns[$classFqn])) {
            return;
        }

        $purgedClassFqns[$classFqn] = true;

        if (!class_exists($classFqn)) {
            throw new \RuntimeException(sprintf(
                'Class "%s" does not exist',
                $classFqn
            ));
        }

        $tableMap = constant($classFqn . '::TABLE_MAP');
        $tableMap = call_user_func($tableMap . '::getTableMap');

        /** @var $relation RelationMap */
        foreach ($tableMap->getRelations() as $name => $relation) {
            $foreignTable = $relation->getLocalTable();
            $relatedClassFqn =  $foreignTable->getClassName();

            switch ($relation->getType()) {
                case RelationMap::ONE_TO_MANY:
                    $this->purgeClassFqn($logger, $relatedClassFqn, $purgedClassFqns);
                    continue;
                case RelationMap::MANY_TO_MANY:
                    $this->purgeClassFqn($logger, $relatedClassFqn, $purgedClassFqns);
                    continue;
            }
        }

        $logger->purgingClassFqn($classFqn);
        $tableMap->doDeleteAll();

        return true;
    }
}
