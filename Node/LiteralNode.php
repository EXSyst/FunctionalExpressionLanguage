<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class LiteralNode extends Node
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
