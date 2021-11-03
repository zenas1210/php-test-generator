<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

use ReflectionProperty;
use Symfony\Component\PropertyInfo\Type;

class Property
{
    private ReflectionProperty $reflection;
    private ?Type $type = null;

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
