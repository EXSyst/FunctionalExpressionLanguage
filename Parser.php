<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\FunctionalExpressionLanguage\Exception\UnexpectedTokenException;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;
use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Node\RootNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\NameNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\LiteralNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\UncertainNode;
use EXSyst\Component\FunctionalExpressionLanguage\Node\FunctionCallNode;
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
        $this->tokens = new TokenIterator();
    }

    public function getRootNode()
    {
        return $this->root;
    }

    public function accept(Token $token)
    {
        // We do not use WHITE_SPACE, COMMENT and EOL
        if ($this->test($token, TokenType::WHITE_SPACE) || $this->test($token, TokenType::EOL) || $this->test($token, TokenType::COMMENT)) {
            return;
        }

        $this->tokens->append($token);

        while ($this->tokens->valid()) {
            $token = $this->tokens->current();
            $this->tokens->next();

            $this->generator->send($token);
        }
    }

    private function parse(): \Generator
    {
        $node = yield from $this->parseExpression();

        $token = yield;
        if (!$token || !$this->test($token, TokenType::EOF)) {
            throw new UnexpectedTokenException($token, TokenType::EOF);
        }

        $this->root->setNode($node);
    }

    private function parseExpression(): \Generator
    {
        if (($node = yield from $this->tryParseFunctionCall())
            || ($node = yield from $this->tryParseName())
            || ($node = yield from $this->tryParseLiteral())) {
            return $node;
        }

        throw new UnexpectedTokenException(yield);
    }

    private function tryParseFunctionCall(): \Generator
    {
        try {
            $function = yield from $this->transact(function() {
                if (!$this->test($name = yield, TokenType::NAME) || !$this->test(yield, TokenType::PUNCTUATION, '(')) {
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

            if ($this->test($token = yield, TokenType::PUNCTUATION, ')')) {
                return $function;
            } elseif (!$this->test($token, TokenType::PUNCTUATION, ',')) {
                throw new UnexpectedTokenException($token, TokenType::PUNCTUATION, ',');
            }
        }
    }

    private function tryParseName(): \Generator
    {
        try {
            return yield from $this->transact(function() {
                if (!$this->test($name = yield, TokenType::NAME)) {
                    throw new \RuntimeException();
                }

                return new NameNode($name->value);
            });
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    private function tryParseLiteral(): \Generator
    {
        try {
            return yield from $this->transact(function() {
                if (!$this->test($literal = yield, TokenType::LITERAL)) {
                    throw new \RuntimeException();
                }

                $value = $literal->value;
                // strings
                if ($pos = max(strrpos($value, '"'), strrpos($value, '\''))) {
                    return new LiteralNode(substr($value, 1, $pos - 1), substr($value, $pos + 1));
                }

                preg_match('/^([0-9]+)(\.[0-9]*)?([^0-9\.]*)$/', $value, $matches);
                if ($matches[1]) {
                    return new LiteralNode(floatval($matches[1].$matches[2]), $matches[3]);
                }

                return new LiteralNode(intval($matches[1]), $matches[3]);
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
        $state = $this->tokens->key();
        try {
            return yield from call_user_func($fn);
        } catch (\Exception $e) {
            $this->tokens->seek($state);

            throw $e;
        }
    }

    private function test(Token $token, $type, $value = null)
    {
        return $token->type === $type && (null === $value || $token->value === $value);
    }
}
