<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;
use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Node\RootNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\NameNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\UncertainNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\FunctionCallNode;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;
use EXSyst\Component\FunctionalExpressionLanguage\Parser\EOFTokenProcessor;
use EXSyst\Component\FunctionalExpressionLanguage\Parser\NameTokenProcessor;
use EXSyst\Component\FunctionalExpressionLanguage\Parser\NameNodeTransformer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;

class Parser implements ParserInterface
{
    private $root;
    private $generator;
    private $operators = array();

    private $tokens = [];

    /**
     * @param Operator[] $operators
     */
    public function __construct(array $operators = array())
    {
        foreach($operators as $operator) {
            $this->registerOperator($operator);
        }

        $this->root = new RootNode();
        $this->generator = $this->parse();
    }

    public function getRootNode()
    {
        return $this->root;
    }

    public function accept(Token $token)
    {
        $this->generator->send($token);

        // Replay tokens
        for (; count($this->tokens); $token = array_shift($this->tokens)) {
            $this->generator->send($token);
        }
    }

    private function parse(): \Generator
    {
        $node = yield from $this->parseExpression();

        $lastToken = yield;
        if (!$lastToken || !$this->test($lastToken, TokenType::EOF)) {
            throw new \LogicException(sprintf('Unexpected token "%s" of value "%s".', TokenType::getName($lastToken->type), $lastToken->value));
        }

        $this->root->setNode($node);
    }

    private function parseExpression(): \Generator
    {
        if ($function = yield from $this->tryParseFunction()) {
            return $function;
        } elseif ($name = yield from $this->tryParseName()) {
            return $name;
        } else {
            $token = yield;

            throw new \RuntimeException(sprintf('Unexpected token of type "%s" and value "%s"', TokenType::getName($token->type), $token->value));
        }
    }

    private function tryParseFunction()
    {
        try {
            $function = yield from $this->transact(function() {
                $name = yield;
                if (!$this->test($name, TokenType::NAME) || !$this->test(yield, TokenType::PUNCTUATION, '(')) {
                    throw new \RuntimeException('not a function');
                }

                return new FunctionCallNode(new NameNode($name->value));
            });
        } catch (\RuntimeException $e) {
            return false;
        }

        try {
            yield from $this->transact(function () {
                if (!$this->test(yield, TokenType::PUNCTUATION, ')')) {
                    throw new \Exception();
                }
            });

            return $function;
        } catch (\Exception $e) {
        }

        while (true) {
            $function->addArgument(yield from $this->parseExpression());

            $token = yield;
            if ($this->test($token, TokenType::PUNCTUATION, ')')) {
                return $function;
            } elseif (!$this->test($token, TokenType::PUNCTUATION, ',')) {
                throw new \RuntimeException('Syntax error: expected ","');
            }
        }
    }

    private function tryParseName()
    {
        try {
            return yield from $this->transact(function() {
                $name = yield;
                if (!$this->test($name, TokenType::NAME)) {
                    throw new \RuntimeException('not a name');
                }

                return new NameNode($name->value);
            });
        } catch (\RuntimeException $e) {
            return false;
        }
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
        $tokens = [];
        try {
            $generator = call_user_func($fn);
            while (true) {
                if (!$generator->valid()) {
                    break;
                }
                $generator->send($tokens[] = yield);
            }

            return $generator->getReturn();
        } catch (\Exception $e) {
            $this->rewind($tokens);

            throw $e;
        }
    }

    private function rewind(array $tokens)
    {
        $this->tokens = array_merge($this->tokens, $tokens);
    }

    private function test(Token $token, $type, $value = null)
    {
        return $token->type === $type && (null === $value || $token->value === $value);
    }
}
