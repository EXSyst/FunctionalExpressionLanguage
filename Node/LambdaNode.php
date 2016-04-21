<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class LambdaNode extends Node
{
    public $arguments;
    public $expression;

    /**
     * @param Node[] $arguments
     * @param Node   $expression
     */
    public function __construct(array $arguments, Node $expression)
    {
        $this->arguments = $arguments;
        $this->expression = $expression;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitLambdaNode($this, $state);
    }
}
