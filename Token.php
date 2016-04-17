<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

class Token
{
    public $type;
    public $value;

    /**
     * @param int   $type see {@link TokenType}
     * @param mixed $value
     */
    public function __construct($type, $value) {
        $this->type = $type;
        $this->value = $value;
    }
}
