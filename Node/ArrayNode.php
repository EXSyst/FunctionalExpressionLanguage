<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class ArrayNode extends Node
{
    public $elements = [];

    public function addElement(Node $value)
    {

        $this->elements[] = $value;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitArrayNode($this, $state);
    }
}
