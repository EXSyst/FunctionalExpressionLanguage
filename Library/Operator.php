<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Library;

class Operator
{
    const LEFT_ASSOCIATION = 0;
    const RIGHT_ASSOCIATION = 1;

    private $name;
    private $precedence;
    private $associativity;

    public function __construct(string $name, int $precedence, int $associativity)
    {
        $this->name = $name;
        $this->precedence = $precedence;
        $this->associativity = $associativity;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPrecedence() : int
    {
        return $this->precedence;
    }

    public function getAssociativity() : int
    {
        return $this->associativity;
    }
}
