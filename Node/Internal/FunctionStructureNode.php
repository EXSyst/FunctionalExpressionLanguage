<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node\Internal;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

/**
 * Only used when parsing.
 * Contains an expression which was parsed between containers ("()", "{}", "[]")
 *
 * @internal
 */
final class FunctionStructureNode extends Node
{
    public $function;
    public $expression;

    /**
     * @param Node $expression
     */
    public function __construct (Node $function, StructureNode $structure = null)
    {
        $this->function = $function;
        if ($structure) {
            $this->expression = $structure->expression;
        }
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitFunctionStructureNode($this, $state);
    }
}
