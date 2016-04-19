<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Exception;

use EXSyst\Component\FunctionalExpressionLanguage\Token;

class ParsingException extends \LogicException
{
    public function __construct($message, Token $token)
    {
        parent::__construct(sprintf('%s (at position %d, line %d, row %d)', $message, $token->cursor, $token->line, $token->row));
    }
}
