<?php

namespace DTL\Spryker\Fixtures\Purger;

interface ProgressLogger
{
    public function purgingClassFqn(string $classFqn);
}
