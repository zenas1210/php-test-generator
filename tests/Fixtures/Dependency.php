<?php

namespace Tests\Fixtures;

class Dependency
{
    public function getSpecific(): ArrayCollection
    {
        return new ArrayCollection();
    }

    public function getInterface(): Collection
    {
        return new ArrayCollection();
    }

    public function getArray(): array
    {
        return [];
    }

    public function validate(): void
    {

    }
}
