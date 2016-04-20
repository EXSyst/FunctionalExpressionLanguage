<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\FunctionalExpressionLanguage\Exception\UnexpectedTokenException;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;
use EXSyst\Component\FunctionalExpressionLanguage\Library\ColonOperator;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\IO\Reader\CDataReader;

class Parser implements ParserInterface
{
    const SEPARATOR_PRECEDENCE = -4;
    const INTEROGATION_PRECEDENCE = -1;

    private $root;
    private $generator;
    private $operators;

    private $tokens = [];

    /**
     * @param Operator[] $operators
     */
    public function __construct(array $operators = array())
    {
        $this->operators = [
            ',' => new Operator(',', self::SEPARATOR_PRECEDENCE, Operator::LEFT_ASSOCIATION),
            ';' => new Operator(';', self::SEPARATOR_PRECEDENCE, Operator::LEFT_ASSOCIATION),
            ':' => new ColonOperator(),
            '=>' => new Operator('=>', -2, Operator::RIGHT_ASSOCIATION),
            '?' => new Operator('?', self::INTEROGATION_PRECEDENCE, Operator::RIGHT_ASSOCIATION),
            '.' => new Operator('.', PHP_INT_MAX - 1, Operator::LEFT_ASSOCIATION),
        ];
        foreach($operators as $operator) {
            $this->registerOperator($operator);
        }

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
        $this->root = new Node\Parsing\StructureNode('(', yield from $this->parseExpression());
        $this->expect(yield, TokenType::EOF);
    }

    private function parseExpression(int $precedence = self::SEPARATOR_PRECEDENCE): \Generator
    {
        if (($expression = yield from $this->tryParseName())
            || ($expression = yield from $this->tryParseLiteral())
            || ($expression = yield from $this->tryParseStructure('[', ']'))
            || ($expression = yield from $this->tryParseStructure('(', ')', false))) {

            $expression = yield from $this->tryParseFunctionCall($expression);

            return yield from $this->tryParseOperation($expression, $precedence);
        }

        throw new UnexpectedTokenException(yield);
    }

    private function tryParseOperation(Node\Node $expression, int $precedence)
    {
        while (true) {
            try {
                $operator = yield from $this->transact(function() use ($expression, $precedence) {
                    $this->expect($operatorName = yield, [TokenType::NAME, TokenType::SYMBOL]);
                    if (!isset($this->operators[$operatorName->value])) {
                        throw new \LogicException(sprintf('Operator "%s" doesn\'t exist', $operatorName->value));
                    }

                    $operator = $this->operators[$operatorName->value];
                    if ($operator->getPrecedence($precedence) < $precedence) {
                        throw new UnexpectedTokenException($operatorName);
                    }

                    return $operator;
                });
            } catch (UnexpectedTokenException $e) {
                break;
            }

            $expression2 = yield from $this->parseExpression(
                Operator::LEFT_ASSOCIATION === $operator->getAssociativity()
                ? $operator->getPrecedence($precedence) + 1
                : $operator->getPrecedence($precedence)
            );
            $expression = new Node\FunctionCallNode(
                new Node\NameNode($operator->getName()),
                [
                    $expression,
                    $expression2,
                ]
            );
        }

        return $expression;
    }

    private function tryParseFunctionCall(Node\Node $expression): \Generator
    {
        if ($structure = yield from $this->tryParseStructure('(', ')')) {
            return new Node\Parsing\FunctionStructureNode($expression, $structure);
        }

        return $expression;
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

    private function tryParseStructure(string $openingTag, string $closingTag, bool $allowEmpty = true): \Generator
    {
        try {
            yield from $this->transact(function() use ($openingTag) {
                $this->expect(yield, TokenType::PUNCTUATION, $openingTag);
            });
        } catch (UnexpectedTokenException $e) {
            return false;
        }

        if ($allowEmpty) {
            // To return clearer errors, try to close the structure
            try {
                yield from $this->transact(function () use ($closingTag) {
                    $this->expect(yield, TokenType::PUNCTUATION, $closingTag);
                });

                return new Node\Parsing\StructureNode($openingTag);
            } catch (UnexpectedTokenException $e) {
            }
        }

        $expression = yield from $this->parseExpression();
        $this->expect(yield, TokenType::PUNCTUATION, $closingTag);

        return new Node\Parsing\StructureNode($openingTag, $expression);
    }

    private function registerOperator(Operator $operator)
    {
        if (isset($this->operators[$operator->getName()])) {
            throw new \LogicException(sprintf('An operator with the name "%s" is already registered', $operator->getName()));
        }

        $name = $operator->getName();
        $len = strlen($name);
        if ((floatval($name) || strspn($name, Lexer::BASE_MASK.Lexer::NUMBERS_MASK) !== $len) && strcspn($name, Lexer::BASE_MASK.Lexer::NUMBERS_MASK.Lexer::PUNCTUATION_MASK.Lexer::QUOTE_MASK.CDataReader::WHITE_SPACE_MASK) !== $len) {
            throw new \LogicException(sprintf('"%s" isn\'t a valid operator. An operator must only be composed of ASCII caracters OR symbols ("matches", "=>" are operators but "1:" or "=3" aren\'t)', $name));
        }

        $this->operators[$operator->getName()] = $operator;
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
            $this->tokens->rewind($state);

            throw $e;
        }
    }

    /**
     * @param int[]|int $types
     */
    private function expect(Token $token, $types, string $value = null)
    {
        if (!$this->test($token, $types, $value)) {
            throw new UnexpectedTokenException($token, $types, $value);
        }
    }

    /**
     * @param int[]|int $types
     */
    private function test(Token $token, $types, string $value = null)
    {
        $types = (array) $types;

        if (null !== $value && $token->value !== $value) {
            return false;
        }

        foreach ($types as $type) {
            if ($type === $token->type) {
                return true;
            }
        }

        return false;
    }
}
