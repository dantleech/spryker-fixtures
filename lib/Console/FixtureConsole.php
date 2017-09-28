<?php

namespace DTL\Spryker\Fixtures\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use DTL\DebugConfig\Config\DebugConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Helper\Table;
use Spryker\Shared\Config\Environment;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Spryker\Zed\Propel\Communication\Plugin\ServiceProvider\PropelServiceProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Propel\Business\PropelFacade;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use DTL\Spryker\Fixtures\Purger\PropelPurger;
use DTL\Spryker\Fixtures\Console\ProgressLogger\OutputProgressLogger;
use DTL\Spryker\Fixtures\Loader\PropelLoader;
use Symfony\Component\Console\Input\InputOption;
use DTL\Spryker\Fixtures\Console\ProgressLogger\NullLogger;
use DTL\Spryker\Fixtures\FixtureLoader\YamlFixtureLoader;

class FixtureConsole extends Console
{
    /**
     * @var PropelPurger
     */
    private $purger;

    /**
     * @var PropelLoader
     */
    private $loader;

    /**
     * @var YamlFixtureLoader
     */
    private $fixtureLoader;

    const COMMAND_NAME = 'inviqa:fixture:load';
    const COMMAND_DESCRIPTION = 'Load fixtures';

    public function __construct(PropelPurger $purger = null, PropelLoader $loader = null)
    {
        parent::__construct();
        $this->purger = $purger ?: new PropelPurger();
        $this->loader = $loader ?: new PropelLoader();
        $this->fixtureLoader = new YamlFixtureLoader();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(static::COMMAND_NAME);
        $this->addOption('no-progress', 'np', InputOption::VALUE_NONE, 'Supress progress');
        $this->setDescription(static::COMMAND_DESCRIPTION);
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to fixture YAML');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        if (false === file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $path
            ));
        }

        $fixtures = $this->fixtureLoader->load($path);

        if ($input->getOption('no-progress')) {
            $progressLogger = new NullLogger();
        } else {
            $progressLogger = new OutputProgressLogger($output);
        }

        $this->purger->purge($progressLogger, array_keys($fixtures));
        $registry = $this->loader->load($progressLogger, $fixtures);

        $output->writeln(json_encode($registry->idMap()));
    }
}

