<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class NameNode extends Node
{
    private $name;
    private $parent;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitNameNode($this);
    }
}
