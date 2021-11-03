<?php

namespace Zenas\PHPTestGenerator\Model;

use ReflectionParameter;
use Symfony\Component\PropertyInfo\Type;

class Parameter
{
    private ReflectionParameter $reflection;
    private ?Type $type = null;

    public function getReflection(): ReflectionParameter
    {
        return $this->reflection;
    }

    public function setReflection(ReflectionParameter $reflection): self
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
