<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Parser;

use EXSyst\Component\FunctionalExpressionLanguage\Node;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;
use EXSyst\Component\FunctionalExpressionLanguage\Parser;
use EXSyst\Component\FunctionalExpressionLanguage\TokenType;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

class EOFTokenProcessor implements TokenProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Token $token, Parser $parser)
    {
        if (Parser::STATE_DEFAULT !== $parser->getState() && Parser::STATE_EXTENSION !== $parser->getState()) {
            throw new \LogicException('Missing tokens.');
        }
        if ($parser->getRootNode() !== $parser->getCurrentNode()) {
            throw new \LogicException('The current node is not root.');
        }

        $parser->setState(Parser::STATE_COMPLETE);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Token $token, Parser $parser): bool
    {
        $currentNode = $parser->getCurrentNode();

        return TokenType::EOF === $token->type;
    }
}
