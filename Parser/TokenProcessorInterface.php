<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Parser;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

interface TokenProcessorInterface
{
    /**
     * @return Node the new current node
     */
    public function process(Token $token, Parser $parser);

    public function supports(Token $token, Parser $parser): bool;
}
