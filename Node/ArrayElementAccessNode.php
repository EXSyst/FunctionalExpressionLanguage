<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class PropertyAccessNode extends Node
{
    private $object;
    private $key;

    /**
     * @param NameNode      $object the object from which access a property
     * @param Node $key    the key of the element to access
     */
    public function __construct(NameNode $object, Node $key)
    {
        $this->object = $object;
        $this->key = $key;
        
        $object->setParent($this);
        $key->setParent($this);
    }

    public function getObject(): NameNode
    {
        return $this->object;
    }

    public function getKey(): Node
    {
        return $this->key;
    }
}
