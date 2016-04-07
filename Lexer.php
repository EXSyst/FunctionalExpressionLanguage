<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\IO\Exception\OverflowException;
use EXSyst\Component\IO\Reader\CDataReader;

class Lexer
{
    /**
     * @param CDataReader|string $source
     */
    public function tokenize($source)
    {
        if (!$source instanceof CDataReader) {
            $source = CDataReader::fromString($source);
        }

        $tokens = [];
        $source->eatWhiteSpace();
        while (!$source->isFullyConsumed()) {
            if ($quote = $source->eatAny(['\'', '"'])) { // strings
                $tokens[] = $this->eatString($source, $quote);
            } elseif ($operator = $this->eatOperator($source)) {
                $tokens[] = $operator;
            } elseif ($name = $this->eatName($source)) {
                $tokens[] = $name;
            } else {
                throw new \Exception(sprintf('Unexpected token %s', $source->eatToFullConsumption()));
            }

            $source->eatWhiteSpace();
        }

        return $tokens;
    }

    /**
     * Eats a literal such as 'This is a string.' or 'this is human\'s'.
     *
     * @param string $quote either ' or ".
     */
    private function eatString(CDataReader $source, $quote)
    {
        $value = '';
        try {
            while (true) {
                $value .= $source->eatCSpan('\\'.$quote);

                $next = $source->read(1);
                if ($next === $quote) {
                    return new Token(TokenType::LITERAL, $quote.$value.$quote);
                } else {
                    $value .= $next;
                    $value .= $source->read(1);
                }
            }
        } catch (OverflowException $exception) {
            throw new \RuntimeException('Unterminated string literal');
        }
    }

    /**
     * Eats an operator.
     */
    private function eatOperator(CDataReader $source)
    {
        if (null !== $operator = $source->eatAny([
            '!==', '===', '==', '!=', // equals
            '>=', '<=', '>', '<', // comparison
            '**', '..', '!', '^', // magic
            '&&', '||', // logic
            '*', '%', '/', '+', '-', // maths
            '~', // concatenation
            '|', '&', // binary
        ])) {
            return new Token(TokenType::OPERATOR, $operator);
        }

        $state = $source->captureState();
        foreach ([
            'not in', 'not', 'and',
            'or', 'in', 'matches',
        ] as $operator) {
            if ($source->eat($operator)) {
                if ($source->eatWhiteSpace() || $source->peek(1) === '(') {
                    return new Token(TokenType::OPERATOR, $operator);
                } else {
                    $state->restore();
                }
            }
        }
    }

    /**
     * Eats a name (may be a variable or a function call).
     */
    private function eatName(CDataReader $source)
    {
        $base = 'ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvxyz_'.implode('', range("\x7f", "\xff"));
        static $numbers = '0123456789';

        if (!$name = $source->eatSpan($base)) {
            return;
        }
        $name .= $source->eatSpan($base.$numbers);

        return new Token(TokenType::NAME, $name);
    }
}
