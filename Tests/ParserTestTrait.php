<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Tests;

use EXSyst\Component\FunctionalExpressionLanguage\Lexer;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;

trait ParserTestTrait
{
    private function getNode($expression, array $operators = array())
    {
        $parser = new Parser($operators);
        $lexer = new Lexer($expression, $parser);

        return $parser->getRootNode();
    }
}
