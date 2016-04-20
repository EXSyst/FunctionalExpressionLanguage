<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class LiteralNode extends Node
{
    public $value;
    public $suffix;

    public function __construct($value, string $suffix = '')
    {
        $this->value = $value;
        $this->suffix = $suffix;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitLiteralNode($this, $state);
    }
}
