<?php

namespace DTL\Spryker\Fixtures\ValueResolver;

class DeferredReference
{
    /**
     * @var string
     */
    private $reference;

    public function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    public function getReference()
    {
        return $this->reference;
    }
}
