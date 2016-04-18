<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class RootNode extends Node
{
    private $node;

    public function setNode(Node $node = null)
    {
        $this->node = $node;
        $node->setParent($this);
    }

    public function getNode()
    {
        return $this->node;
    }

    public function setParent()
    {
        throw new \LogicException('A root node can\'t have a parent.');
    }
}
