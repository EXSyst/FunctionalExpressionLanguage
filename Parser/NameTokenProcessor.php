<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Parser;

use EXSyst\Component\FunctionalExpressionLanguage\Node;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

class NameTokenProcessor implements TokenProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, Parser $parser)
    {
        $node = new Node\ExpressionNode(new Node\NameNode($token->value));

        $currentNode = $parser->getCurrentNode();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Token $token, Parser $parser): bool
    {
        $currentNode = $parser->getCurrentNode();

        return TokenType::NAME === $token->type && Parser::STATE_DEFAULT === $parser->getState();
    }
}
