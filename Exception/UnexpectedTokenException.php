<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Exception;

use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;

class UnexpectedTokenException extends SyntaxException
{
    public function __construct(Token $token, int $expectedType = null, $expectedValue = null)
    {
        $message = sprintf('Unexpected token "%s" of value "%s"', TokenType::getName($token->type), $token->value);
        if ($expectedType) {
            $message .= sprintf(' ("%s" expected%s)', TokenType::getName($expectedType), $expectedValue ? sprintf(' with value "%s"', $expectedValue) : '');
        }

        parent::__construct($message, $token);
    }
}
