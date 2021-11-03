<?php

namespace Zenas\PHPTestGenerator\Generator\PHPUnit;

interface ValueFactoryInterface
{
    public function getValueForType(?string $type);

    public function supports(?string $type): bool;
}