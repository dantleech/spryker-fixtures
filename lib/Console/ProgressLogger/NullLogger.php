<?php

namespace DTL\Spryker\Fixtures\Console\ProgressLogger;

use DTL\Spryker\Fixtures\Loader\ProgressLogger as LoaderProgressLogger;
use DTL\Spryker\Fixtures\Purger\ProgressLogger as PurgerProgressLogger;
use Symfony\Component\Console\Output\OutputInterface;

class NullLogger implements PurgerProgressLogger, LoaderProgressLogger
{
    public function purgingClassFqn(string $classFqn)
    {
    }

    public function loadingClassFqn(string $classFqn)
    {
    }

    public function loadedClassFqn(string $classFqn)
    {
    }

    public function loadingFixture(string $name)
    {
    }
}
