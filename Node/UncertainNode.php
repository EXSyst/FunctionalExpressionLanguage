<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

/**
 * Allows to change a node later during the parsing process.
 */
final class UncertainNode implements NodeInterface
{
    private $node;
    private $parent;

    public function setNode(Node $node = null)
    {
        $this->node = $node;
        $node->setParent($this);
    }

    public function getNode()
    {
        return $this->node;
    }
}
