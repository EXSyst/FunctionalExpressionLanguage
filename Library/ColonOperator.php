<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Library;

use EXSyst\Component\FunctionalExpressionLanguage\Parser;

/**
 * @internal
 */
class ColonOperator
{
    public function getName()
    {
        return ':'; // -1
    }

    public function getPrecedence(int $actualPrecedence): int
    {
        if (Parser::INTEROGATION_PRECEDENCE === $actualPrecedence) {
            return Parser::INTEROGATION_PRECEDENCE;
        }

        return -3;
    }

    public function getAssociativity(): int
    {
        return Operator::RIGHT_ASSOCIATION;
    }
}
