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
    const ARGUMENT_PATH = 'path';
    const OPTION_PARAMETERS = 'parameters';
    const OPTION_PURGE = 'purge';
    const OPTION_NO_PROGRESS = 'no-progress';


    public function __construct(PropelPurger $purger = null)
    {
        parent::__construct();
        $this->purger = $purger ?: new PropelPurger();
        $this->fixtureLoader = new YamlFixtureLoader();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESCRIPTION);

        $this->addArgument(
            self::ARGUMENT_PATH,
            InputArgument::REQUIRED,
            'Path to fixture YAML'
        );

        $this->addOption(
            self::OPTION_PARAMETERS,
            null,
            InputOption::VALUE_REQUIRED,
            'JSON encoded parameters for fixture file'
        );
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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument(self::ARGUMENT_PATH);
        $parameters = $this->resolveParameters($input->getOption(self::OPTION_PARAMETERS));
        $additionalClassesToPurge = $input->getOption(self::OPTION_PURGE);
        $fixtures = [];

        if (false === file_exists($path)) {
            throw new InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $path
            ));
        }

        $fixtures = $this->fixtureLoader->load($path);
        $progressLogger = $this->progressLogger($input, $output);

        $this->purger->purge($progressLogger, $this->classesToPurge($fixtures, $additionalClassesToPurge));
        $registry = $this->createLoader($parameters)->load($progressLogger, $fixtures);

        $output->writeln(json_encode($registry->idMap()));
    }

    private function resolveParameters(string $parameters = null)
    {
        if (null === $parameters) {
            return [];
        }

        $parameters = json_decode($parameters, true);

        if (false === $parameters) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON parameters: %s',
                json_last_error()
            ));
        }

        return $parameters;
    }

    private function progressLogger(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_NO_PROGRESS)) {
            return new NullLogger();
        }

        return new OutputProgressLogger($output);
    }

    private function createLoader(array $parameters)
    {
        return new PropelLoader($parameters);
    }

    private function classesToPurge($fixtures, $additionalClassesToPurge): array
    {
        return array_merge(
            array_keys($fixtures),
            $additionalClassesToPurge
        );
    }
}
