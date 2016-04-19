<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class FunctionCallNode extends Node
{
    private $name;
    private $arguments = [];

    /**
     * @param NameNode        $name
     * @param Node[] $arguments
     */
    public function __construct(NameNode $name, array $arguments = array())
    {
        $name->setParent($this);
        $this->name = $name;

        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
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
        $argument->setParent($this);
    }
}
