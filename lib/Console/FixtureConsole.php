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

    const COMMAND_NAME = 'inviqa:fixture:load';
    const COMMAND_DESCRIPTION = 'Load fixtures';

    public function __construct(PropelPurger $purger = null, PropelLoader $loader = null)
    {
        parent::__construct();
        $this->purger = $purger ?: new PropelPurger();
        $this->loader = $loader ?: new PropelLoader();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESCRIPTION);
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to fixture YAML');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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

        $contents = file_get_contents($path);
        $fixtures = Yaml::parse($contents);

        $progressLogger = new OutputProgressLogger($output);

        $this->purger->purge($progressLogger, array_keys($fixtures));
        $this->loader->load($progressLogger, $fixtures);

        $output->write(PHP_EOL);
    }
}

