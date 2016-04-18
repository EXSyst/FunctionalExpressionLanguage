<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Parser;

use EXSyst\Component\FunctionalExpressionLanguage\Node\Node;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

interface TokenTransformerInterface
{
    /**
     * @return Node the new current node
     */
    public function transform(Token $token, Parser $parser);

    public function supports(Token $token, Parser $parser): bool;
}
