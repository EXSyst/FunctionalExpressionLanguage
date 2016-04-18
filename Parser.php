<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;
use EXSyst\Component\FunctionalExpressionLanguage\Node\RootNode;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;
use EXSyst\Component\FunctionalExpressionLanguage\Parser\NameNodeTransformer;

class Parser implements ParserInterface
{
    /**
     * Default state, accept any expression.
     */
    const STATE_DEFAULT = 0;
    /**
     * Extension state, check if the current expression can be extended.
     */
    const STATE_EXTENSION = 0;
    /**
     * Incomplete state, the parser needs a character such as a parenthesis.
     */
    const STATE_INCOMPLETE = 1;

    private $root;
    private $currentNode;
    private $state = self::STATE_DEFAULT;
    private $finished = false;
    private $operators = array();
    private $tokenTransformers;

    /**
     * @param Operator[] $operators
     */
    public function __construct(array $operators)
    {
        $this->tokenTransformers = [
            new NameNodeTransformer(),
        ];

        foreach($operators as $operator) {
            $this->registerOperator($operator);
        }

        $this->currentNode = $this->root = new RootNode();
    }

    public function accept(Token $token)
    {
        foreach ($this->tokenTransformers as $transformer) {
            if ($transformer->supports($token, $this)) {
                $transformer->transform($token, $this);

                return;
            }
        }

        throw new \LogicException(sprintf('Unexpected token "%s" of value "%s".', TokenType::getName($token->type), $token->value));
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state)
    {
        $this->state = $state;
    }

    public function getRootNode(): Node
    {
        return $this->root;
    }

    public function getCurrentNode(): Node
    {
        return $this->currentNode;
    }

    public function setCurrentNode(Node $node)
    {
        $this->currentNode = $node;
    }

    private function registerOperator(Operator $operator)
    {
        if (isset($this->operators[$operator->getName()])) {
            throw new \LogicException(sprintf('An operator with the name "%s" is already registered', $operator->getName()));
        }

        $this->operators[$operator->getName] = $operator;
    }

    /**
     * Try a callable and return to the previous state if there is an error.
     */
    private function transact(callable $fn): \Generator
    {
        $state = $this->state;
        try {
            return yield from call_user_func($fn);
        } catch (\Exception $e) {
            $this->state = $state;

            throw $e;
        }
    }

    private function test(TokenInterface $token, $type, $value = null)
    {
        return $token->type === $type && (null === $value || $token->value === $value);
    }
}
