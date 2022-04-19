<?php

namespace Tests\Fixtures;

use InvalidArgumentException;

class TestedService
{
    /** @var Dependency */
    private $provider;

    /** @var array */
    private $config;

    public function __construct(Dependency $dependency, array $cfg)
    {
        $this->provider = $dependency;
        $this->config = $cfg;
    }

    public function doSomething(Collection $interfaceCollection, ArrayCollection $concreteCollection, string $key)
    {
        $this->provider->validate();

        $items = $this->provider->getArray();

        $item = $this->provider->getSpecific();
    }

    public function returnSomething(bool $throw): string
    {
        if ($throw) {
            throw new InvalidArgumentException();
        }

        return 'something';
    }
}
