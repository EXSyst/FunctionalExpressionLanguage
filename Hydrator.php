<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\FunctionalExpressionLanguage\Node;

class Hydrator
{
    public function createName(string $name)
    {
        return new Node\NameNode($name);
    }

    public function createLiteral(string $literal)
    {
        // strings
        if ($pos = max(strrpos($literal, '"'), strrpos($literal, '\''))) {
            return new Node\LiteralNode(substr($literal, 1, $pos - 1), substr($literal, $pos + 1));
        }

        preg_match('/^([0-9]+)(\.[0-9]*)?([^0-9\.]*)$/', $literal, $matches);
        if ($matches[1]) {
            return new Node\LiteralNode(floatval($matches[1].$matches[2]), $matches[3]);
        }

        return new Node\LiteralNode(intval($matches[1]), $matches[3]);
    }

    public function createOperation(string $name, Node\Node $expression1, Node\Node $expression2)
    {
        if ('=>' === $name) {
            throw new \LogicException('Use createLambda() instead of considering "=>" as an operator');
        }

        return new Node\FunctionCallNode($this->createName($name), [$expression1, $expression2]);
    }

    public function createLambda(Node\Node $arguments = null, Node\Node $expression) {
        if ($arguments) {
            $arguments = $this->split($arguments);
            foreach ($arguments as $argument) {
                if (!$argument instanceof Node\NameNode) {
                    throw new \LogicException('Lambdas only accept variables as argument');
                }
            }
        }

        return new Node\LambdaNode($arguments ?? [], $expression);
    }

    public function createFunctionCall(Node\Node $function, Node\Node $arguments = null)
    {
        if (null === $arguments) {
            return new Node\FunctionCallNode($function);
        }

        $arguments = $this->split($arguments);
        foreach ($arguments as $argument) {
            if ($this->isColon($argument)) {
                throw new \LogicException('You can\'t use named expressions when calling a function');
            }
        }

        return new Node\FunctionCallNode($function, $arguments);
    }

    public function createScope(Node\Node $inner = null)
    {
        if (null === $inner) {
            throw new \LogicException('A scope must contain expressions');
        }

        $expressions = $this->split($inner);

        $assignments = [];
        $returnExpression = null;
        foreach ($expressions as $expression) {
            if ($this->isColon($expression)) {
                if (!$expression->arguments[0] instanceof Node\NameNode) {
                    throw new \LogicException('You can\'t assign a value to an expression');
                }

                $assignments[$expression->arguments[0]->name] = $expression->arguments[1];
            } else {
                if (null !== $returnExpression) {
                    throw new \LogicException('A scope can\'t contain more than one anonymous expression');
                }

                $returnExpression = $expression;
            }
        }

        if (null === $returnExpression) {
            throw new \LogicException('A scope must contain one anonymous expression');
        }

        if (empty($assignments)) {
            return $returnExpression;
        }

        return new Node\ScopeNode($assignments, $returnExpression);
    }

    public function createArray(Node\Node $elements = null)
    {
        if (null === $elements) {
            return new Node\ArrayNode();
        }

        $elements = $this->split($elements);
        foreach ($elements as $element) {
            if ($this->isColon($element)) {
                throw new \LogicException('An array can only contain anonymous expressions');
            }
        }

        return new Node\ArrayNode($elements);
    }

    /**
     * @return Node[]
     */
    private function split(Node\Node $expression): array
    {
        if (!$this->isSeparator($expression)) {
            return [$expression];
        }

        $expressions = $this->split($expression->arguments[0]);
        $expressions[] = $expression->arguments[1];

        return $expressions;
    }

    private function isSeparator(Node\Node $expression): bool
    {
        return $this->isOperator($expression, ',') || $this->isOperator($expression, ';');
    }

    private function isColon(Node\Node $expression): bool
    {
        return $this->isOperator($expression, ':');
    }

    private function isOperator(Node\Node $expression, string $operator): bool
    {
        if (!$this->isNamedFunction($expression) || $expression->function->name !== $operator) {
            return false;
        }

        return true;
    }

    private function isNamedFunction(Node\Node $expression): bool
    {
        return $expression instanceof Node\FunctionCallNode && $expression->function instanceof Node\NameNode;
    }
}
