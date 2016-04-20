<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class ArrayNode extends Node
{
    public $elements = [];
    private $index;

    public function __construct()
    {
        $this->index = -1;
    }

    public function addElement(Node $value, Node $key = null)
    {
        if (null === $key) {
            $key = new LiteralNode(++$this->index);
        }

        $this->elements[$key] = $value;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitArrayNode($this, $state);
    }
}
