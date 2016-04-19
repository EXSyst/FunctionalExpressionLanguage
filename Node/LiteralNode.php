<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class LiteralNode extends Node
{
    private $value;
    private $suffix;

    public function __construct($value, $suffix)
    {
        $this->value = $value;
        $this->suffix = $suffix;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }
}
