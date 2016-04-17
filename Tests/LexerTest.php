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
        $tokens[] = new Token(TokenType::EOF, null);

        $lexer = new Lexer();
        $this->assertEquals($tokens, $lexer->tokenize($expression));
    }

    public function getTokenizeData()
    {
        return [
            'spaces' => [[], "\r\t   \t\n "],
            'operators' => [
                [
                    new Token(TokenType::LITERAL, '\'foo\'suffix'),
                    new Token(TokenType::PUNCTUATION, '.'),
                    new Token(TokenType::NAME, 'bar'),
                    new Token(TokenType::SYMBOL, '=!='),
                    new Token(TokenType::LITERAL, '\'bar\''),
                ],
                "  'foo'suffix.bar =!= 'bar' \t",
            ],
            'strings' => [
                [
                    new Token(TokenType::LITERAL, '"# @ foo"su'),
                    new Token(TokenType::LITERAL, '\'bar\\\\\\\'foo\'re'),
                ],
                '"# @ foo"su   \'bar\\\\\\\'foo\'re',
            ],
            'integers' => [
                [
                    new Token(TokenType::LITERAL, '12d'),
                    new Token(TokenType::LITERAL, '1382f'),
                ],
                ' 12d 1382f',
            ],
            'floats' => [
                [
                    new Token(TokenType::LITERAL, '1.234f'),
                    new Token(TokenType::LITERAL, '54.a'),
                    new Token(TokenType::LITERAL, '3.'),
                ],
                ' 1.234f 54.a 3.',
            ],
            'variables' => [
                [
                    new Token(TokenType::NAME, 'my_var'),
                    new Token(TokenType::SYMBOL, '==='),
                    new Token(TokenType::LITERAL, '"foo"'),
                ],
                '  my_var === "foo" ',
            ],

        ];
    }
}
