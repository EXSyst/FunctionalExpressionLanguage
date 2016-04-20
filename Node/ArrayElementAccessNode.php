<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class ArrayElementAccessNode extends Node
{
    private $array;
    private $key;

    /**
     * @param Node $object the object from which access a property
     * @param Node $key    the key of the element to access
     */
    public function __construct(Node $array, Node $key)
    {
        $this->array = $array;
        $this->key = $key;
    }

    public function getArray(): Node
    {
        return $this->array;
    }

    public function getKey(): Node
    {
        return $this->key;
    }
}
