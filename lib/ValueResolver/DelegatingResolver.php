<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

use InvalidArgumentException;

class DelegatingResolver implements ValueResolver
{
    /**
     * @var array
     */
    private $resolvers = [];

    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolveValue(array $valueConfig)
    {
        if (!isset($valueConfig['type'])) {
            throw new InvalidArgumentException(
                'Expected "type" key in value configuration'
            );
        }

        $type = $valueConfig['type'];

        if (!isset($this->resolvers[$type])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown value type "%s", valid types: "%s"',
                $type, implode('", "', array_keys($this->resolvers))
            ));
        }

        unset($valueConfig['type']);

        return $this->resolvers[$type]->resolveValue($valueConfig);
    }
}
