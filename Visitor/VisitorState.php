<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;

class VisitorState
{
    private $ancestors;
    public $current;
    private $userData;

    public function __construct(array $ancestors, Node &$current, \ArrayObject $userData = null)
    {
        if (null === $userData) {
            $userData = new \ArrayObject();
        }

        $this->ancestors = $ancestors;
        $this->current = &$current;
        $this->userData = $userData;
    }

    public function replaceCurrent(Node $newNode) {
        $this->current = $newNode;
    }

    public function createChild(Node &$child)
    {
        return new self(array_merge([ $this->current ], $this->ancestors), $child, $this->userData);
    }
}
