<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Parser;

use EXSyst\Component\FunctionalExpressionLanguage\Node;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

class NameNodeTransformer implements TokenTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform(Token $token, Parser $parser)
    {
        $node = new Node\UncertainNode(new Node\NameNode($token->value));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Token $token, Parser $parser): bool
    {
        $currentNode = $parser->getCurrentNode();
        
        return TokenType::NAME === $token && Parser::STATE_DEFAULT === $parser->getState();
    }
}
