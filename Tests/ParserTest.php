<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Token;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Library\Operator;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    use ParserTestTrait;

    /**
     * @dataProvider getTokenizeData
     */
    public function testTokenize($node, $expression, array $operators = array())
    {
        $this->assertEquals($node, $this->getNode($expression, $operators));
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
                        )
                    ]
                ),
                'foo(bar, foo(foo))',
            ],
            'property_access' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('.'),
                    [
                        new Node\NameNode('foo'),
                        new Node\NameNode('bar'),
                    ]
                ),
                'foo.bar',
            ],
            'literals' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('foo'),
                    [
                        new Node\LiteralNode('my_string', 'suffixed'),
                        new Node\LiteralNode(32, 'bar'),
                        new Node\LiteralNode(1.23, 'kg'),
                    ]
                ),
                'foo("my_string"suffixed, 32bar, 1.23kg)',
            ],
            'scopes' => [
                new Node\ScopeNode(
                    [
                        'bar' => new Node\ScopeNode(
                            [
                                'baz' => new Node\LiteralNode(4),
                            ],
                            new Node\NameNode('baz')
                        )
                    ],
                    new Node\NameNode('foo')
                ),
                'bar: (baz: 4; baz); foo'
            ],
            'lambda' => [
                new Node\LambdaNode(
                    [
                        new Node\NameNode('x'),
                        new Node\NameNode('y')
                    ],
                    new Node\NameNode('foo')
                ),
                '(x, y) => foo'
            ],
            'test precedence of ?: operator' => [
                new Node\ScopeNode(
                    [
                        'bar' => new Node\FunctionCallNode(
                            new Node\NameNode('?'),
                            [
                                new Node\NameNode('foo'),
                                new Node\FunctionCallNode(
                                    new Node\NameNode(':'),
                                    [
                                        new Node\NameNode('quz'),
                                        new Node\NameNode('baz'),
                                    ]
                                ),
                            ]
                        ),
                    ],
                    new Node\NameNode('bar')
                ),
                'bar: foo ? quz : baz; bar'
            ],
            'test custom operators' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('plus'),
                    [
                        new Node\LiteralNode(4),
                        new Node\FunctionCallNode(
                            new Node\NameNode('*'),
                            [
                                new Node\LiteralNode(2),
                                new Node\FunctionCallNode(
                                    new Node\NameNode('**'),
                                    [
                                        new Node\LiteralNode(3),
                                        new Node\LiteralNode(4),
                                    ]
                                )
                            ]
                        )
                    ]
                ),
                '4 plus 2 * 3 ** 4',
                [
                    new Operator('plus', 20, Operator::LEFT_ASSOCIATION),
                    new Operator('*', 25, Operator::LEFT_ASSOCIATION),
                    new Operator('**', 200, Operator::RIGHT_ASSOCIATION),
                ]
            ],
            'test array' => [
                new Node\ArrayNode(
                    [
                        new Node\NameNode('foo'),
                        new Node\LiteralNode('bar'),
                    ]
                ),
                '[foo, "bar"]',
            ],
            'test complex function call' => [
                new Node\FunctionCallNode(
                    new Node\LambdaNode(
                        [
                            new Node\NameNode('x'),
                        ],
                        new Node\NameNode('bar')
                    )
                ),
                '(x => bar)()',
            ],
            'test complex function call priority' => [
                new Node\LambdaNode(
                    [
                        new Node\NameNode('x')
                    ],
                    new Node\FunctionCallNode(
                        new Node\NameNode('bar')
                    )
                ),
                'x => bar()',
            ],
        ];
    }

    /**
     * @dataProvider equivalenceProvider
     */
    // public function testEquivalence(array $operators, $expression, ...$equivalences)
    // {
    //     $node = $this->getNode($expression, $operators);
    //     foreach ($equivalences as $equivalence) {
    //         $this->assertEquals($node, $this->getNode($equivalence));
    //     }
    // }
    //
    // public function equivalenceProvider()
    // {
    //     return [
    //         [
    //             [ new Operator('+', 15, Operator::LEFT_ASSOCIATION) ],
    //             'a + b + c',
    //             '+(+(a, b), c)'
    //         ]
    //     ];
    // }

    /**
     * @expectedException EXSyst\Component\FunctionalExpressionLanguage\Exception\UnexpectedTokenException
     * @expectedExceptionMessage Unexpected token "PUNCTUATION" of value ")" (at position 9, line 0, row 9)
     */
    public function testFunctionArgumentsSyntaxError()
    {
        $this->getNode('foo(bar !)', array(new Operator('!', '10', Operator::LEFT_ASSOCIATION)));
    }

    /**
     * @dataProvider invalidOperatorsProvider
     */
    public function testInvalidOperators($operator)
    {
        try {
            new Parser(array(new Operator($operator, 20, Operator::LEFT_ASSOCIATION)));

            $this->fail(sprintf('"%s" is considered as valid but shouldn\'t.', $operator));
        } catch (\LogicException $e) {
            $this->assertContains(sprintf('"%s" isn\'t a valid operator', $operator), $e->getMessage());
        }
    }

    public function invalidOperatorsProvider()
    {
        return [
            [' '],
            ['=o'],
            ['match@s'],
            [')'],
            ['{'],
        ];
    }
}
