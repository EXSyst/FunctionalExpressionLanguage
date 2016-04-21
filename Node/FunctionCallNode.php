<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class FunctionCallNode extends Node
{
    public $function;
    public $arguments;

    /**
     * @param Node   $function
     * @param Node[] $arguments
     */
    public function __construct(Node $function, array $arguments = [])
    {
        $this->function = $function;
        $this->arguments = $arguments;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitFunctionCallNode($this, $state);
    }
}
