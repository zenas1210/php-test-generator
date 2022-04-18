<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

class ChainValueFactory implements ValueFactoryInterface
{
    /** @var ValueFactoryInterface[] */
    private $factories;

    public function __construct(array $factories = [])
    {
        $this->factories = $factories;
    }

    public function setFactories(array $factories = [])
    {
        $this->factories = $factories;
    }

    public function getValueForType(?string $type)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($type)) {
                return $factory->getValueForType($type);
            }
        }

        return null;
    }

    public function supports(?string $type): bool
    {
        return true;
    }
}
