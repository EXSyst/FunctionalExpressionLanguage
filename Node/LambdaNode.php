<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

final class LambdaNode extends Node
{
    private $arguments;
    private $expression;

    /**
     * @param NameNode[] $arguments
     * @param Node       $expression
     */
    public function __construct(array $arguments, Node $expression)
    {
        $this->arguments = $arguments;
        $this->expression = $expression;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getExpression(): Node
    {
        return $this->expression;
    }

    public function addArgument(Node $argument)
    {
        $this->arguments[] = $argument;
    }
}
