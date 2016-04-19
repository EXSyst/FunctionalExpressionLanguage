<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\FunctionalExpressionLanguage\Exception\UnexpectedTokenException;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;
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

        $this->root = new Node\ScopeNode();
        $this->tokens = new TokenIterator();
        $this->generator = $this->parse();

        // Run the code until the first yield
        $this->generator->valid();
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
        $node = yield from $this->parseScopeInner($this->root);
        $this->expect(yield, TokenType::EOF);
    }

    private function parseExpression(): \Generator
    {
        if (($node = yield from $this->tryParseFunctionCall())
            || ($node = yield from $this->tryParseName())
            || ($node = yield from $this->tryParseLiteral())
            || ($node = yield from $this->tryParseScope())) {
            return $node;
        }

        throw new UnexpectedTokenException(yield);
    }

    private function tryParseFunctionCall(): \Generator
    {
        try {
            $function = yield from $this->transact(function() {
                $this->expect($name = yield, TokenType::NAME);
                $this->expect(yield, TokenType::PUNCTUATION, '(');

                return new Node\FunctionCallNode(new Node\NameNode($name->value));
            });
        } catch (UnexpectedTokenException $e) {
            return false;
        }

        try {
            yield from $this->transact(function () {
                $this->expect(yield, TokenType::PUNCTUATION, ')');
            });

            return $function;
        } catch (UnexpectedTokenException $e) {
        }

        while (true) {
            $function->addArgument(yield from $this->parseExpression());

            if ($this->test($token = yield, TokenType::PUNCTUATION, ')')) {
                return $function;
            }

            $this->expect($token, TokenType::PUNCTUATION, ',');
        }
    }

    private function tryParseName(): \Generator
    {
        try {
            return yield from $this->transact(function() {
                $this->expect($name = yield, TokenType::NAME);

                return new Node\NameNode($name->value);
            });
        } catch (UnexpectedTokenException $e) {
            return false;
        }
    }

    private function tryParseLiteral(): \Generator
    {
        try {
            $value = yield from $this->transact(function() {
                $this->expect($literal = yield, TokenType::LITERAL);

                return $literal->value;
            });
        } catch (UnexpectedTokenException $e) {
            return false;
        }

        // strings
        if ($pos = max(strrpos($value, '"'), strrpos($value, '\''))) {
            return new Node\LiteralNode(substr($value, 1, $pos - 1), substr($value, $pos + 1));
        }

        preg_match('/^([0-9]+)(\.[0-9]*)?([^0-9\.]*)$/', $value, $matches);
        if ($matches[1]) {
            return new Node\LiteralNode(floatval($matches[1].$matches[2]), $matches[3]);
        }

        return new Node\LiteralNode(intval($matches[1]), $matches[3]);
    }

    private function tryParseScope(): \Generator
    {
        try {
            yield from $this->transact(function() {
                $this->expect(yield, TokenType::PUNCTUATION, '(');
            });
        } catch (UnexpectedTokenException $e) {
            return false;
        }

        $node = new Node\ScopeNode();

        yield from $this->parseScopeInner($node);

        $this->expect(yield, TokenType::PUNCTUATION, ')');

        return $node;
    }

    private function parseScopeInner(Node\ScopeNode $node)
    {
        while (true) {
            try {
                $name = yield from $this->transact(function() {
                    $this->expect($name = yield, TokenType::NAME);
                    $this->expect(yield, TokenType::PUNCTUATION, ':');

                    return $name;
                });
            } catch (UnexpectedTokenException $e) {
                break;
            }

            $node->addAssignment($name->value, yield from $this->parseExpression());
            $this->expect(yield, TokenType::PUNCTUATION, ';');
        }

        $node->setExpression(yield from $this->parseExpression());
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
        // The stream is already on the next token
        $state = $this->tokens->key();

        try {
            return yield from call_user_func($fn);
        } catch (\Exception $e) {
            $this->tokens->rewind($state);

            throw $e;
        }
    }

    private function expect(Token $token, int $type, string $value = null)
    {
        if (!$this->test($token, $type, $value)) {
            throw new UnexpectedTokenException($token, $type, $value);
        }
    }

    private function test(Token $token, int $type, string $value = null)
    {
        return $token->type === $type && (null === $value || $token->value === $value);
    }
}
