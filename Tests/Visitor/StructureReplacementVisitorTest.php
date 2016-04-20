<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests\Visitor;

use EXSyst\Component\FunctionalExpressionLanguage\Node;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\StructureReplacementVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Tests\ParserTestTrait;

class StructureReplacementVisitorTest extends \PHPUnit_Framework_TestCase
{
    use ParserTestTrait;

    protected function setUp()
    {
        $this->visitor = new StructureReplacementVisitor();
    }

    public function testFunctionStructure()
    {
        $this->visitor->visit($root = $this->getNode('foo(a, b)'));

        $this->assertEquals(
            new Node\Internal\StructureNode(
                '(',
                new Node\FunctionCallNode(
                    new Node\NameNode('foo'),
                    [
                        new Node\NameNode('a'),
                        new Node\NameNode('b'),
                    ]
                )
            ),
            $root
        );
    }
}
