<?php

declare(strict_types=1);

namespace Zenas\PHPTestGenerator\Model;

class GeneratedTestClass
{
    /** @var string */
    private $testClassName;

    /** @var string */
    private $code;

    public function __construct(string $testClassName, string $code)
    {
        $this->testClassName = $testClassName;
        $this->code = $code;
    }

    public function getTestClassName(): string
    {
        return $this->testClassName;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
