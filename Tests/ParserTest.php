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
    /**
     * @dataProvider getTokenizeData
     */
    public function testTokenize($node, $expression, array $operators = array())
    {
        $this->assertEquals(new Node\Parsing\StructureNode('(', $node), $this->getNode($expression, $operators));
    }

    public function getTokenizeData()
    {
        return [
            'call' =>[
                new Node\Parsing\FunctionStructureNode(new Node\NameNode('foo'), new Node\Parsing\StructureNode('(')),
                'foo()',
            ],
            'nested_calls' => [
                new Node\Parsing\FunctionStructureNode(
                    new Node\NameNode('foo'),
                    new Node\Parsing\StructureNode('(',
                        new Node\FunctionCallNode(
                            new Node\NameNode(','),
                            [
                                new Node\NameNode('bar'),
                                new Node\Parsing\FunctionStructureNode(
                                    new Node\NameNode('foo'),
                                    new Node\Parsing\StructureNode('(',
                                        new Node\NameNode('foo')
                                    )
                                ),
                            ]
                        )
                    )
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
                new Node\Parsing\FunctionStructureNode(
                    new Node\NameNode('foo'),
                    new Node\Parsing\StructureNode('(',
                        new Node\FunctionCallNode(
                            new Node\NameNode(','),
                            [
                                new Node\FunctionCallNode(
                                    new Node\NameNode(','),
                                    [
                                        new Node\LiteralNode('my_string', 'suffixed'),
                                        new Node\LiteralNode(32, 'bar')
                                    ]
                                ),
                                new Node\LiteralNode(1.23, 'kg'),
                            ]
                        )
                    )
                ),
                'foo("my_string"suffixed, 32bar, 1.23kg)',
            ],
            'scopes' => [
                // bar: (...); foo
                new Node\FunctionCallNode(
                    new Node\NameNode(';'),
                    [
                        // bar: (...)
                        new Node\FunctionCallNode(
                            new Node\NameNode(':'),
                            [
                                new Node\NameNode('bar'),
                                // (baz: 4; baz)
                                new Node\Parsing\StructureNode(
                                    '(',
                                    // baz: 4; baz
                                    new Node\FunctionCallNode(
                                        new Node\NameNode(';'),
                                        [
                                            // baz: 4
                                            new Node\FunctionCallNode(
                                                new Node\NameNode(':'),
                                                [
                                                    new Node\NameNode('baz'),
                                                    new Node\LiteralNode(4),
                                                ]
                                            ),
                                            // baz
                                            new Node\NameNode('baz'),
                                        ]
                                    )
                                )
                            ]
                        ),
                        // foo
                        new Node\NameNode('foo'),
                    ]
                ),
                'bar: (baz: 4; baz); foo'
            ],
            'lambda' => [
                // (...) => ...
                new Node\FunctionCallNode(
                    new Node\NameNode('=>'),
                    [
                        // (x, y)
                        new Node\Parsing\StructureNode(
                            '(',
                            new Node\FunctionCallNode(
                                new Node\NameNode(','),
                                [
                                    new Node\NameNode('x'),
                                    new Node\NameNode('y'),
                                ]
                            )
                        ),
                        // foo
                        new Node\NameNode('foo'),
                    ]
                ),
                '(x, y) => foo'
            ],
            'test precedence of ?: operator' => [
                new Node\FunctionCallNode(
                    new Node\NameNode(':'),
                    [
                        new Node\NameNode('bar'),
                        new Node\FunctionCallNode(
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
                    ]
                ),
                'bar: foo ? quz : baz'
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
                new Node\Parsing\StructureNode(
                    '[',
                    new Node\FunctionCallNode(
                        new Node\NameNode(':'),
                        [
                            new Node\NameNode('foo'),
                            new Node\NameNode('bar'),
                        ]
                    )
                ),
                '[foo: bar]',
            ],
            'test complex function call' => [
                new Node\Parsing\FunctionStructureNode(
                    new Node\Parsing\StructureNode(
                        '(',
                        new Node\FunctionCallNode(
                            new Node\NameNode('=>'),
                            [
                                new Node\NameNode('x'),
                                new Node\NameNode('bar'),
                            ]
                        )
                    ),
                    new Node\Parsing\StructureNode('(', null)
                ),
                '(x => bar)()',
            ],
            'test complex function call priority' => [
                new Node\FunctionCallNode(
                    new Node\NameNode('=>'),
                    [
                        new Node\NameNode('x'),
                        new Node\Parsing\FunctionStructureNode(
                            new Node\NameNode('bar'),
                            new Node\Parsing\StructureNode('(', null)
                        ),
                    ]
                ),
                'x => bar()',
            ],
        ];
    }

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

    private function getNode($expression, array $operators = array())
    {
        $parser = new Parser($operators);
        $lexer = new Lexer($expression, $parser);

        return $parser->getRootNode();
    }
}
