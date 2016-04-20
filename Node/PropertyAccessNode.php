<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class PropertyAccessNode extends Node
{
    private $object;
    private $property;

    /**
     * @param NameNode $object the object from which access a property
     * @param Node $property the property to access or the function to call
     */
    public function __construct(Node $object, Node $property)
    {
        $this->object = $object;
        $this->property = $property;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitPropertyAccessNode($this);
    }
}
