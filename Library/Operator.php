<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Library;

final class Operator
{
    const LEFT_ASSOCIATION = 0;
    const RIGHT_ASSOCIATION = 1;

    private $name;
    private $priority;
    private $associativity;

    public function __construct(string $name, int $priority, int $associativity)
    {
        $this->name = $name;
        $this->priority = $priority;
        $this->associativity = $associativity;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPriority() : int
    {
        return $this->priority;
    }

    public function getAssociativity() : bool
    {
        return $this->associativity;
    }
}
