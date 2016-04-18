<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

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
}
