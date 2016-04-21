<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Exception;

use EXSyst\Component\FunctionalExpressionLanguage\Token;

class StopTransactionException extends \Runtime
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
