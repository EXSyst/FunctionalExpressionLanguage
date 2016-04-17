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
            'spaces' => [
                [
                    new Token(TokenType::EOL, "\r\n", 0, 0, 0),
                    new Token(TokenType::WHITE_SPACE, "  \t", 2, 1, 0),
                    new Token(TokenType::EOL, "\n", 5, 1, 3),
                    new Token(TokenType::EOF, null, 6, 2, 0),
                ],
                "\r\n  \t\n"
            ],
            'operators' => [
                [
                    new Token(TokenType::WHITE_SPACE, '  ', 0, 0, 0),
                    new Token(TokenType::LITERAL, '\'foo\'suffix', 2, 0, 2),
                    new Token(TokenType::PUNCTUATION, '.', 13, 0, 13),
                    new Token(TokenType::NAME, 'bar', 14, 0, 14),
                    new Token(TokenType::SYMBOL, '=!=', 17, 0, 17),
                    new Token(TokenType::LITERAL, '\'bar\'', 20, 0, 20),
                    new Token(TokenType::EOF, null, 25, 0, 25),
                ],
                "  'foo'suffix.bar=!='bar'",
            ],
            'strings' => [
                [
                    new Token(TokenType::LITERAL, "\"# @\n\n foo\"su", 0, 0, 0),
                    new Token(TokenType::LITERAL, '\'bar\\\\\\\'foo\'re', 13, 2, 7),
                    new Token(TokenType::EOF, null, 27, 2, 21),
                ],
                "\"# @\n\n foo\"su'bar\\\\\\'foo're",
            ],
            'integers' => [
                [
                    new Token(TokenType::LITERAL, '12d', 0, 0, 0),
                    new Token(TokenType::EOL, "\n", 3, 0, 3),
                    new Token(TokenType::LITERAL, '1382f', 4, 1, 0),
                    new Token(TokenType::EOF, null, 9, 1, 5),
                ],
                "12d\n1382f",
            ],
            'floats' => [
                [
                    new Token(TokenType::LITERAL, '1.234f', 0, 0, 0),
                    new Token(TokenType::WHITE_SPACE, ' ', 6, 0, 6),
                    new Token(TokenType::LITERAL, '54.a', 7, 0, 7),
                    new Token(TokenType::WHITE_SPACE, ' ', 11, 0, 11),
                    new Token(TokenType::LITERAL, '3.', 12, 0, 12),
                    new Token(TokenType::EOF, null, 14, 0, 14),
                ],
                '1.234f 54.a 3.',
            ],
            'variables' => [
                [
                    new Token(TokenType::NAME, 'my_var', 0, 0, 0),
                    new Token(TokenType::SYMBOL, '===', 6, 0, 6),
                    new Token(TokenType::LITERAL, '"foo"', 9, 0, 9),
                    new Token(TokenType::EOF, null, 14, 0, 14),
                ],
                'my_var==="foo"',
            ],
            'single_line_comments' => [
                [
                    new Token(TokenType::NAME, 'foo', 0, 0, 0),
                    new Token(TokenType::COMMENT, '// my comment === "super"', 3, 0, 3),
                    new Token(TokenType::EOL, "\n\r", 28, 0, 28),
                    new Token(TokenType::NAME, 'bar', 30, 1, 0),
                    new Token(TokenType::EOL, "\r", 33, 1, 3),
                    new Token(TokenType::COMMENT, '-- hello world', 34, 2, 0),
                    new Token(TokenType::EOF, null, 48, 2, 14),
                ],
                "foo// my comment === \"super\"\n\rbar\r-- hello world"
            ],
            'multi_line_comments' => [
                [
                    new Token(TokenType::COMMENT, "/* my long\ncomment*/", 0, 0, 0),
                    new Token(TokenType::EOF, null, 20, 1, 9),
                ],
                "/* my long\ncomment*/"
            ]
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unterminated string literal
     */
    public function testUnterminatedLiteral()
    {
        $lexer = new Lexer();
        $lexer->tokenize('"literal');
    }
}
