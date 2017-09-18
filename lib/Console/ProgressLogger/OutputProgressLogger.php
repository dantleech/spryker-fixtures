<?php

namespace DTL\Spryker\Fixtures\Console\ProgressLogger;

use DTL\Spryker\Fixtures\Purger\ProgressLogger as PurgerProgressLogger;
use DTL\Spryker\Fixtures\Loader\ProgressLogger as LoaderProgressLogger;
use Symfony\Component\Console\Output\OutputInterface;

class OutputProgressLogger implements PurgerProgressLogger, LoaderProgressLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function purgingClassFqn(string $classFqn)
    {
        $this->output->writeln('<comment>Purging: </>' . $classFqn);
    }

    public function loadingClassFqn(string $classFqn)
    {
        $this->output->write('<info>Loading: </>' . $classFqn);
    }

    public function loadingFixture(string $name)
    {
        $this->output->write('.');
    }
}
