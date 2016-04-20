<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node;

/**
 * Visits all the node of the tree.
 */
abstract class NodeVisitor
{
    public function visit(Node\Node &$node, VisitorState $state = null)
    {
        if (null === $state) {
            $state = new VisitorState([], $node);
        }

        $node->accept($this, $state);
    }

    public function visitNameNode(Node\NameNode $node, VisitorState $state)
    {
    }

    public function visitArrayElementAccessNode(Node\ArrayElementAccessNode $node, VisitorState $state)
    {
        $this->visitChild($node->array, $state);
        $this->visitChild($node->key, $state);
    }

    public function visitArrayNode(Node\ArrayNode $node, VisitorState $state)
    {
        foreach ($node->elements as &$value) {
            $this->visitChild($value, $state);
        }
    }

    public function visitFunctionCallNode(Node\FunctionCallNode $node, VisitorState $state)
    {
        $this->visitChild($node->function, $state);
        foreach ($node->arguments as &$argument) {
            $this->visitChild($argument, $state);
        }
    }

    public function visitLambdaNode(Node\LambdaNode $node, VisitorState $state)
    {
        foreach ($node->arguments as &$argument) {
            $this->visitChild($argument, $state);
        }

        $this->visitChild($node->expression, $state);
    }

    public function visitLiteralNode(Node\LiteralNode $node, VisitorState $state)
    {
    }

    public function visitPropertyAccessNode(Node\PropertyAccessNode $node, VisitorState $state)
    {
        $this->visitChild($node->object, $state);
        $this->visitChild($node->property, $state);
    }

    public function visitScopeNode(Node\ScopeNode $node, VisitorState $state)
    {
        foreach ($node->assignments as &$assignment) {
            $this->visitChild($assignment, $state);
        }

        $this->visitChild($node->expression, $state);
    }

    // Internal
    public function visitFunctionStructureNode(Node\Internal\FunctionStructureNode $node, VisitorState $state)
    {
        $this->visitChild($node->function, $state);
        if ($expression = $node->expression) {
            $this->visitChild($expression, $state);
        }
    }

    public function visitStructureNode(Node\Internal\StructureNode $node, VisitorState $state)
    {
        if ($node->expression) {
            $this->visitChild($node->expression, $state);
        }
    }

    protected function visitChild(Node\Node $node, VisitorState $state)
    {
        $this->visit($node, $state->createChild($node));
    }
}
