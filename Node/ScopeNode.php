<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

final class ScopeNode extends Node
{
    public $assignments;
    public $expression;

    /**
     * @param AssignmentNode $assignments
     * @param Node           $expression
     */
    public function __construct (array $assignments = array(), Node $expression = null)
    {
        $this->assignments = $assignments;
        $this->expression = $expression;
    }

    public function addAssignment(string $name, Node $assignment)
    {
        if (isset($this->assignments[$name])) {
            throw new \LogicException(sprintf('Assignment for %s already exists.', $name));
        }

        $this->assignments[$name] = $assignment;
    }

    public function accept(NodeVisitor $visitor, VisitorState $state)
    {
        return $visitor->visitScopeNode($this, $state);
    }
}
