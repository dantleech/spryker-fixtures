<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

use InvalidArgumentException;

class ParameterResolver implements ValueResolver
{
    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function resolveValue(array $valueConfig)
    {
        if (!isset($valueConfig['name'])) {
            throw new InvalidArgumentException(
                'Expected `name` key in parameter configuration'
            );
        }

        $name = $valueConfig['name'];

        if (false === isset($this->parameters[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Could not find parameters "%s", known parameters "%s"',
                $name, implode('", "', array_keys($this->parameters))
            ));
        }

        return $this->parameters[$name];
    }
}
