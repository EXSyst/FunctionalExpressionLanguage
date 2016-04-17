<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

final class TokenType
{
    const LITERAL = 0;
    const SYMBOL = 1;
    const OPERATOR = 2;
    const NAME = 3;
    const PUNCTUATION = 4;
    const EOF = 5;

    public static function getName($type)
    {
        switch ($type) {
            case self::LITERAL: return 'LITERAL';
            case self::SYMBOL: return 'SYMBOL';
            case self::NAME: return 'NAME';
            case self::PUNCTUATION: return 'PUNCTUATION';
            case self::EOF: return 'EOF';
        }
    }
}
