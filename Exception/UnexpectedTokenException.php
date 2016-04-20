<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Exception;

use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;

class UnexpectedTokenException extends SyntaxException
{
    /**
     * @param int[]|int $expectedTypes
     */
    public function __construct(Token $token, $expectedTypes = array(), $expectedValue = null)
    {
        $expectedTypes = (array) $expectedTypes;

        $message = sprintf('Unexpected token "%s" of value "%s"', TokenType::getName($token->type), $token->value);
        if ($expectedTypes) {
            $expectedTypes = implode('", "', array_map([TokenType::class, 'getName'], $expectedTypes));
            $message .= sprintf(' ("%s" expected%s)', $expectedTypes, $expectedValue ? sprintf(' with value "%s"', $expectedValue) : '');
        }

        parent::__construct($message, $token);
    }
}
