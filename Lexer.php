<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

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
        while (!$source->isFullyConsumed()) {
            $source->eatWhiteSpace();
            if ($quote = $source->eatAny(['\'', '"'])) { // strings
                $tokens[] = $this->eatString($source, $quote);
            } elseif ($operator = $this->eatOperator($source)) {
                $tokens[] = $operator;
            } else {
                throw new \Exception(sprintf('Unexpected token %s', $source->eatToFullConsumption()));
            }
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

        while (true) {
            $value .= $source->eatCSpan('\\'.$quote);

            $next = $source->read(1);
            if ($next === $quote) {
                return new Token(TokenType::LITERAL, stripcslashes($value));
            } else {
                $value .= $next;
                $value .= $source->read(1);
            }

            if ($source->isFullyConsumed()) {
                throw new \Exception('source consummed');
            }
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
}
