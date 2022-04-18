<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

use ReflectionProperty;
use Symfony\Component\PropertyInfo\Type;

class Property
{
    /** @var ReflectionProperty */
    private $reflection;

    /** @var Type|null */
    private $type;

    public function getReflection(): ReflectionProperty
    {
        return $this->reflection;
    }

    public function setReflection(ReflectionProperty $reflection): self
    {
        $this->reflection = $reflection;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}
