<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

final class FunctionCallNode extends Node
{
    private $function;
    private $arguments;

    /**
     * @param Node   $function
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

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitFunctionCallNode($this);
    }
}
