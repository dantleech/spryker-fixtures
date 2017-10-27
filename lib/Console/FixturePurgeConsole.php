<?php

namespace DTL\Spryker\Fixtures\Console;

use DTL\DebugConfig\Config\DebugConfig;
use DTL\Spryker\Fixtures\Console\ProgressLogger\NullLogger;
use DTL\Spryker\Fixtures\Console\ProgressLogger\OutputProgressLogger;
use DTL\Spryker\Fixtures\FixtureLoader\YamlFixtureLoader;
use DTL\Spryker\Fixtures\Loader\PropelLoader;
use DTL\Spryker\Fixtures\Purger\PropelPurger;
use InvalidArgumentException;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use RuntimeException;
use Spryker\Shared\Config\Environment;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Propel\Business\PropelFacade;
use Spryker\Zed\Propel\Communication\Plugin\ServiceProvider\PropelServiceProvider;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\Yaml\Yaml;

class FixturePurgeConsole extends Console
{
    /**
     * @var PropelPurger
     */
    private $purger;

    const COMMAND_NAME = 'inviqa:fixture:purge';
    const COMMAND_DESCRIPTION = 'Recursively purge by class name';
    const OPTION_PURGE = 'purge';
    const OPTION_NO_PROGRESS = 'no-progress';

    public function __construct(PropelPurger $purger = null)
    {
        parent::__construct();
        $this->purger = $purger ?: new PropelPurger();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESCRIPTION);

        $this->addOption(
            self::OPTION_PURGE,
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Purge class (in addition to fixture classes)'
        );

        $this->addOption(
            self::OPTION_NO_PROGRESS,
            'np',
            InputOption::VALUE_NONE,
            'Supress progress'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $classesToPurge = $input->getOption(self::OPTION_PURGE);
        $progressLogger = $this->progressLogger($input, $output);

        $this->purger->purge($progressLogger, $classesToPurge);
    }

    private function progressLogger(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_NO_PROGRESS)) {
            return new NullLogger();
        }

        return new OutputProgressLogger($output);
    }
}
