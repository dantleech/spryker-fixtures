<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

use InvalidArgumentException;
use DTL\Spryker\Fixtures\ValueResolver\ValueResolver;

class DeferredResolver implements ValueResolver
{
    public function resolveValue(array $valueConfig)
    {
        if (!isset($valueConfig['reference'])) {
            throw new InvalidArgumentException(sprintf(
                'Expected `reference` key in value configuration for "%s"'
            , 'value'));
        }

        return new DeferredReference($valueConfig['reference']);
    }
}
