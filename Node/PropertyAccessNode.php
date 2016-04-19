<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class PropertyAccessNode extends Node
{
    private $object;
    private $property;

    /**
     * @param NameNode $object the object from which access a property
     * @param Node $property the property to access or the function to call
     */
    public function __construct(NameNode $object, Node $property)
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
}
