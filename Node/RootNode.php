<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;

final class RootNode extends Node
{
    private $node;

    public function __construct ($node = null)
    {
        $this->setNode($node);
    }

    public function setNode(Node $node = null)
    {
        $this->node = $node;
    }
}
