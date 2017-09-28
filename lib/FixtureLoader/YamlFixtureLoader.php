<?php

namespace DTL\Spryker\Fixtures\FixtureLoader;

use Symfony\Component\Yaml\Yaml;

class YamlFixtureLoader
{
    public function load(string $filePath)
    {
        return $this->loadFile($filePath);
    }

    private function loadFile(string $filePath, string $baseDir = null)
    {
        // relative to including fixture
        if ($baseDir && substr($filePath, 0, 1) !== '/') {
            $filePath = $baseDir . '/' . $filePath;
        }

        if (false === file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist', $filePath
            ));
        }

        $contents = file_get_contents($filePath);
        $fixtures = Yaml::parse($contents);

        $includedFixtures = [];
        if (isset($fixtures['_include'])) {
            foreach ($fixtures['_include'] as $includeFile) {
                $filePath = realpath($filePath);
                $initialDir = dirname($filePath);
                $includedFixtures[] = $this->loadFile($includeFile, $initialDir);
            }

            unset($fixtures['_include']);
        }

        $includedFixtures[] = $fixtures;

        return array_reduce($includedFixtures, function ($acc, $fixtures) {
            foreach ($fixtures as $classFqn => $data) {
                if (false === isset($acc[$classFqn])) {
                    $acc[$classFqn] = [];
                }

                $acc[$classFqn] = array_merge($acc[$classFqn], $data);
            }

            return $acc;
        }, []);
    }
}
