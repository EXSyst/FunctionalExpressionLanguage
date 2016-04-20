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
        } else {
            $state = $state->createChild($node);
        }

        $node->accept($this, $state);
    }

    public function visitNameNode(Node\NameNode $node, VisitorState $state)
    {
    }

    public function visitArrayElementAccessNode(Node\ArrayElementAccessNode $node, VisitorState $state)
    {
        $this->visit($node->array, $state);
        $this->visit($node->key, $state);
    }

    public function visitArrayNode(Node\ArrayNode $node, VisitorState $state)
    {
        foreach ($node->elements as &$key => &$value) {
            if ($key instanceof Node\Node) {
                $this->visit($key, $state);
            }
            $this->visit($value, $state);
        }
    }

    public function visitFunctionCallNode(Node\FunctionCallNode $node, VisitorState $state)
    {
        $this->visit($node->function, $state);
        foreach ($node->arguments as &$argument) {
            $this->visit($argument, $state);
        }
    }

    public function visitLambdaNode(Node\LambdaNode $node, VisitorState $state)
    {
        foreach ($node->arguments as &$argument) {
            $this->visit($argument, $state);
        }

        $this->visit($node->expression, $state);
    }

    public function visitLiteralNode(Node\LiteralNode $node, VisitorState $state)
    {
    }

    public function visitPropertyAccessNode(Node\PropertyAccessNode $node, VisitorState $state)
    {
        $this->visit($node->object, $state);
        $this->visit($node->property, $state);
    }

    public function visitScopeNode(Node\ScopeNode $node, VisitorState $state)
    {
        foreach ($node->assignments as &$assignment) {
            $this->visit($assignment, $state);
        }

        $this->visit($node->expression, $state);
    }

    // Parsing
    public function visitFunctionStructureNode(Node\Parsing\FunctionStructureNode $node, VisitorState $state)
    {
        $this->visit($node->function, $state);
        if ($expression = $node->expression) {
            $this->visit($expression, $state);
        }
    }

    public function visitStructureNode(Node\Parsing\StructureNode $node, VisitorState $state)
    {
        if ($node->expression) {
            $this->visit($node->expression, $state);
        }
    }
}
