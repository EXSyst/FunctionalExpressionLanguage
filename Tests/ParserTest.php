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
        $this->assertEquals(new Node\ScopeNode(array(), $node), $this->getNode($expression));
    }

    public function getTokenizeData()
    {
        return [
            'call' =>[
                new Node\FunctionCallNode(new Node\NameNode('foo')),
                'foo()',
            ],
            'nested_calls' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('foo'),
                    [
                        new Node\NameNode('bar'),
                        new Node\FunctionCallNode(
                            new Node\NameNode('foo'),
                            [
                                new Node\NameNode('foo'),
                            ]
                        ),
                    ]
                ),
                'foo(bar, foo(foo))',
            ],
            // 'property_access' => [
            //     null,
            //     'foo.bar',
            // ],
            'literals' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('foo'),
                    [
                        new Node\LiteralNode('my_string', 'suffixed'),
                        new Node\LiteralNode('my_other_string', ''),
                        new Node\LiteralNode(32, 'bar'),
                        new Node\LiteralNode(1.23, 'kg'),
                    ]
                ),
                'foo("my_string"suffixed, \'my_other_string\', 32bar, 1.23kg)',
            ],
            'scopes' => [
                new Node\ScopeNode(
                    [
                        'bar' => new Node\ScopeNode(['baz' => new Node\LiteralNode(4)], new Node\NameNode('baz')),
                        'foo' => new Node\FunctionCallNode(new Node\NameNode('foo'), [new Node\NameNode('bar')]),
                    ],
                    new Node\NameNode('foo')
                ),
                '(bar: (baz: 4; baz); foo: foo(bar); foo)'
            ]
        ];
    }

    public function testRootScope()
    {
        $this->assertEquals(new Node\ScopeNode(['bar' => new Node\LiteralNode('foo')], new Node\LiteralNode('bar')), $this->getNode('bar: "foo"; "bar"'));
    }

    /**
     * @expectedException EXSyst\Component\FunctionalExpressionLanguage\Exception\UnexpectedTokenException
     * @expectedExceptionMessage Unexpected token "PUNCTUATION" of value ";" ("PUNCTUATION" expected with value ",") (at position 7, line 0, row 7)
     */
    public function testFunctionArgumentsSyntaxError()
    {
        $this->getNode('foo(bar;)');
    }

    private function getNode($expression)
    {
        $parser = new Parser();
        $lexer = new Lexer($expression, $parser);

        return $parser->getRootNode();
    }
}
