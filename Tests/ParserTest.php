<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Node;

class ParserTest extends \PHPUnit_Framework_TestCase
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
            'function' =>[
                new Node\RootNode(new Node\FunctionCallNode(new Node\NameNode('foo'))),
                'foo()',
            ],
            'names' => [
                new Node\RootNode(new Node\FunctionCallNode(new Node\NameNode('foo'))),
                'foo(bar, foo)',
            ]
        ];
    }
}
