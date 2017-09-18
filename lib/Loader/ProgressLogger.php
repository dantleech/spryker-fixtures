<?php

namespace DTL\Spryker\Fixtures\Loader;

interface ProgressLogger
{
    public function loadingFixture(string $classFqn);
    public function loadingClassFqn(string $classFqn);
    public function loadedClassFqn(string $classFqn);
}

