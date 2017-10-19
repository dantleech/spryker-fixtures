<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

use InvalidArgumentException;

class ConstantResolver implements ValueResolver
{
    public function resolveValue(array $valueConfig)
    {
        if (!isset($valueConfig['constant'])) {
            throw new InvalidArgumentException(sprintf(
                'Expected `constant` key in value configuration for "%s"'
            , 'constant'));
        }

        return constant($valueConfig['constant']);
    }
}
