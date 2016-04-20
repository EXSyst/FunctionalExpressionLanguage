<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node;

class StructureReplacementVisitor extends NodeVisitor
{
    public function visitFunctionStructureNode(Node\Internal\FunctionStructureNode $node, VisitorState $state)
    {
        $this->visitChild($node->function, $state);
        if ($expression = $node->expression) {
            $arguments = $this->split($expression);
        }

        $state->replaceCurrent(new Node\FunctionCallNode($node->function, $arguments ?? []));
    }

    /**
     * @return Node[]
     */
    private function split(Node\Node $expression): array
    {
        if (!$this->isSeparator($expression)) {
            return [$expression];
        }

        return [
            $this->split($expression->arguments[0]),
            $expression->arguments[1],
        ];
    }

    private function isSeparator(Node\Node $expression): bool
    {
        if (!$expression instanceof Node\FunctionCallNode || !$expression->function instanceof Node\NameNode) {
            return false;
        }
        if ($expression->function->name !== ',' && $expression->function->name !== ';') {
            return false;
        }

        return true;
    }
}
