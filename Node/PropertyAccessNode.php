<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class PropertyAccessNode extends Node
{
    public $object;
    public $property;

    /**
     * @param Node $object the object from which access a property
     * @param Node $property the property to access or the function to call
     */
    public function __construct(Node $object, Node $property)
    {
        $this->object = $object;
        $this->property = $property;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitPropertyAccessNode($this, $state);
    }
}
