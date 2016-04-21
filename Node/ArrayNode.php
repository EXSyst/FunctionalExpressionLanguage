<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class ArrayNode extends Node
{
    public $elements;

    /**
     * @param Node[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitArrayNode($this, $state);
    }
}
