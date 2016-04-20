<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class LambdaNode extends Node
{
    private $arguments;
    private $expression;

    /**
     * @param Node $arguments
     * @param Node $expression
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

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitLambdaNode($this);
    }
}
