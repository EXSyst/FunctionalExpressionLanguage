<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class FunctionCallNode extends Node
{
    private $name;
    private $arguments;

    /**
     * @param NameNode        $name
     * @param Node[] $arguments
     */
    public function __construct(NameNode $name, array $arguments = array())
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function addArgument(Node $argument)
    {
        $this->arguments[] = $argument;
    }
}
