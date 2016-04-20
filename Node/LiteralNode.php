<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class LiteralNode extends Node
{
    private $value;
    private $suffix;

    public function __construct($value, string $suffix = '')
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

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitLiteralNode($this);
    }
}
