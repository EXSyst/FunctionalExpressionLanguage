<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node\Parsing;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;

/**
 * Only used when parsing.
 * Contains an expression which was parsed between containers ("()", "{}", "[]")
 *
 * @internal
 */
final class FunctionStructureNode extends Node
{
    private $function;
    private $arguments;

    /**
     * @param Node $expression
     */
    public function __construct (Node $function, StructureNode $arguments)
    {
        $this->function = $function;
        $this->arguments = $arguments->getExpression();
    }

    public function getFunction(): Node
    {
        return $this->function;
    }

    public function getArguments(): Node
    {
        return $this->arguments;
    }
}
