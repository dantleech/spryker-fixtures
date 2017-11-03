<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

use InvalidArgumentException;
use DTL\Spryker\Fixtures\ValueResolver\ValueResolver;

class LiteralResolver implements ValueResolver
{
    public function resolveValue(array $valueConfig)
    {
        if (!isset($valueConfig['value'])) {
            throw new InvalidArgumentException(sprintf(
                'Expected `value` key in value configuration for "%s"'
            , 'value'));
        }

        return $valueConfig['value'];
    }
}
