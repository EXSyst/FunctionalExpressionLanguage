<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class ArrayElementAccessNode extends Node
{
    public $array;
    public $key;

    /**
     * @param Node $object the object from which access a property
     * @param Node $key    the key of the element to access
     */
    public function __construct(Node $array, Node $key)
    {
        $this->array = $array;
        $this->key = $key;
    }
}
