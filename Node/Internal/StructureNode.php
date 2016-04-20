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
final class StructureNode extends Node
{
    public $expression;

    /**
     * @param Node $expression
     */
    public function __construct (string $openingTag, Node $expression = null)
    {
        $this->openingTag = $openingTag;
        $this->expression = $expression;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitStructureNode($this, $state);
    }
}
