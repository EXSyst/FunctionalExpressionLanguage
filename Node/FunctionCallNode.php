<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class FunctionCallNode extends Node
{
    private $function;
    private $arguments;

    /**
     * @param Node        $function
     * @param Node[] $arguments
     */
    public function __construct(Node $function, array $arguments = array())
    {
        $this->function = $function;
        $this->arguments = $arguments;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}
