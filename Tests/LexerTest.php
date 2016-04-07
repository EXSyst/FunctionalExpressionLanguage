<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    private $lexer;

    protected function setUp()
    {
        $this->lexer = new Lexer();
    }

    public function testLiterals()
    {
        $source = <<<EOF
            "foo"   'bar\\\\\\'foo'
EOF;
        $tokens = $this->lexer->tokenize($source);

        $this->assertEquals([
            new Token(TokenType::LITERAL, '"foo"'),
            new Token(TokenType::LITERAL, '\'bar\\\\\\\'foo\''),
        ], $tokens);
    }

    public function testOperators()
    {
        $source = <<<EOF
            'foo' === 'bar'
EOF;
        $tokens = $this->lexer->tokenize($source);

        $this->assertEquals([
            new Token(TokenType::LITERAL, '\'foo\''),
            new Token(TokenType::OPERATOR, '==='),
            new Token(TokenType::LITERAL, '\'bar\''),
        ], $tokens);
    }
}
