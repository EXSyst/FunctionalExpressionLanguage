<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node\Parsing;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

/**
 * Only used when parsing.
 * Contains an expression which was parsed between containers ("()", "{}", "[]")
 *
 * @internal
 */
final class FunctionStructureNode extends Node
{
    private $function;
    private $expression;

    /**
     * @param Node $expression
     */
    public function __construct (Node $function, StructureNode $expression = null)
    {
        $this->function = $function;
        $this->expression = $expression->getExpression();
    }

    public function getFunction(): Node
    {
        return $this->function;
    }

    public function getExpression(): Node
    {
        return $this->expression;
    }

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitFunctionStructureNode($this);
    }
}
