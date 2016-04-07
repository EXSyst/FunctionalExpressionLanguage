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
                    new Token(TokenType::LITERAL, '\'foo\''),
                    new Token(TokenType::OPERATOR, '==='),
                    new Token(TokenType::LITERAL, '\'bar\''),
                ],
                "  'foo' === 'bar' \t"
            ],
            'literals' => [
                [
                    new Token(TokenType::LITERAL, '"# @ foo"'),
                    new Token(TokenType::LITERAL, '\'bar\\\\\\\'foo\''),
                ],
                '"# @ foo"   \'bar\\\\\\\'foo\''
            ],
            'names' => [
                [
                    new Token(TokenType::NAME, 'my_var'),
                    new Token(TokenType::OPERATOR, '==='),
                    new Token(TokenType::LITERAL, '"foo"'),
                ],
                '  my_var === "foo" ',
            ],
        ];
    }
}
