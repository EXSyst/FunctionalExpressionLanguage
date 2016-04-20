<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node\Parsing;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;

/**
 * Only used when parsing.
 * Contains an expression which was parsed between containers ("()", "{}", "[]")
 *
 * @internal
 */
final class StructureNode extends Node
{
    private $expression;

    /**
     * @param Node $expression
     */
    public function __construct (string $openingTag, Node $expression = null)
    {
        $this->openingTag = $openingTag;
        $this->expression = $expression;
    }

    public function getExpression()
    {
        return $this->expression;
    }
}
