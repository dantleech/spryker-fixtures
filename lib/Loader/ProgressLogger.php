<?php

namespace DTL\Spryker\Fixtures\Loader;

interface ProgressLogger
{
    public function loadingFixture(string $classFqn);
}

