<?php

namespace Compiler;

class Token
{
    public function __construct(
        public string $type,
        public string $value
    ) {}

    public function __toString(): string
    {
        return "[{$this->type}: {$this->value}]";
    }
}

?>