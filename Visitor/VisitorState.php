<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;

class VisitorState
{
    private $ancestors;
    private $current;
    private $userData;

    public function __construct(array $ancestors, Node &$current, \ArrayObject $userData)
    {
        $this->ancestors = $ancestors;
        $this->current =& $current;
        $this->userData = $userData;
    }

    public function createChild(Node &$child)
    {
        return new self(array_merge([ $this->current ], $this->ancestors), $child, $this->userData);
    }
}
