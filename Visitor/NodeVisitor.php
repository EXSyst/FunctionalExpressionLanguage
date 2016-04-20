<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node;

/**
 * Visits all the node of the tree.
 */
abstract class NodeVisitor
{
    public function visit(Node\Node $node)
    {
        $node->accept($this);
    }

    public function visitNameNode(Node\NameNode $node)
    {
    }

    public function visitArrayElementAccessNode(Node\ArrayElementAccessNode $node)
    {
        $node->getArray()->visit($this);
        $node->getKey()->visit($this);
    }

    public function visitArrayNode(Node\ArrayNode $node)
    {
        foreach ($node->getElements() as $key => $value) {
            if ($key instanceof Node\Node) {
                $key->accept($this);
            }
            $value->accept($this);
        }
    }

    public function visitFunctionCallNode(Node\FunctionCallNode $node)
    {
        $node->getFunction()->accept($this);
        foreach ($node->getArguments() as $argument) {
            $argument->accept($this);
        }
    }

    public function visitLambdaNode(Node\LambdaNode $node)
    {
        foreach ($node->getArguments() as $argument) {
            $argument->accept($this);
        }

        $node->getExpression()->accept($this);
    }

    public function visitLiteraleNode(Node\LiteralNode $node)
    {
    }

    public function visitPropertyAccessNode(Node\PropertyAccessNode $node)
    {
        $node->getObject()->accept($this);
        $node->getProperty()->accept($this);
    }

    public function visitScopeNode(Node\ScopeNode $node)
    {
        foreach ($node->getAssignments() as $assignment) {
            $assignment->accept($this);
        }

        $node->getExpression()->accept($this);
    }

    // Parsing
    public function visitFunctionStructureNode(Node\Parsing\FunctionStructureNode $node)
    {
        $node->getFunction()->accept($this);
        if ($expression = $node->getExpression()) {
            $expression->accept($this);
        }
    }

    public function visitStructureNode(Node\Parsing\StructureNode $node)
    {
        if ($expression = $node->getExpression()) {
            $expression->accept($this);
        }
    }
}
