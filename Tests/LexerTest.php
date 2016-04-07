<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTokenizeData
     */
    public function testTokenize($tokens, $expression)
    {
        $lexer = new Lexer();
        $this->assertEquals($tokens, $lexer->tokenize($expression));
    }

    public function getTokenizeData()
    {
        return [
            'spaces' => [[], "\r\t   \t\n "],
            'operators' => [
                [
                    new Token(TokenType::STRING, '\'foo\''),
                    new Token(TokenType::OPERATOR, '==='),
                    new Token(TokenType::STRING, '\'bar\''),
                ],
                "  'foo' === 'bar' \t"
            ],
            'strings' => [
                [
                    new Token(TokenType::STRING, '"# @ foo"'),
                    new Token(TokenType::STRING, '\'bar\\\\\\\'foo\''),
                ],
                '"# @ foo"   \'bar\\\\\\\'foo\''
            ],
            'integers' => [
                [
                    new Token(TokenType::INTEGER, '12'),
                    new Token(TokenType::INTEGER, '1382'),
                ],
                ' 12 1382'
            ],
            'floats' => [
                [
                    new Token(TokenType::FLOAT, '1.234'),
                    new Token(TokenType::INTEGER, '54'),
                    new Token(TokenType::PUNCTUATION, '.'),                    
                ],
                ' 1.234 54.'
            ],
            'variables' => [
                [
                    new Token(TokenType::NAME, 'my_var'),
                    new Token(TokenType::OPERATOR, '==='),
                    new Token(TokenType::STRING, '"foo"'),
                ],
                '  my_var === "foo" ',
            ],
        ];
    }
}
