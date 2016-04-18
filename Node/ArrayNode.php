<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

class ArrayNode extends Node
{
    private $elements = [];
    private $index;

    public function __construct()
    {
        $this->index = -1;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function addElement(Node $value, Node $key = null)
    {
        if (null === $key) {
            $key = new LiteralNode(++$this->index);
        }

        $this->elements[$key] = $value;

        $value->setParent($this);
        $key->setParent($this);
    }
}
