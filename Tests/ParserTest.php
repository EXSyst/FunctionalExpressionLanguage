<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTokenizeData
     */
    public function testTokenize($node, $expression)
    {
        $parser = new Parser();
        $lexer = new Lexer($expression, $parser);
        $this->assertEquals($node, $parser->getRootNode());
    }

    public function getTokenizeData()
    {
        return [
            'names' => [
                new RootNode(new UncertainNode(new NameNode('foo'))),
                'foo',
            ]
        ];
    }
}
