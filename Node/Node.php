<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

abstract class Node
{
    private $parent;

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }
}
