<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\Node;

interface ParserInterface
{
    public function accept(Token $token);
}
