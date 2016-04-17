<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

class Token
{
    public $type;
    public $value;
    public $cursor;
    public $line;
    public $row;

    /**
     * @param int   $type see {@link TokenType}
     * @param mixed $value
     */
    public function __construct($type, $value, $cursor, $line, $row) {
        $this->type = $type;
        $this->value = $value;
        $this->cursor = $cursor;
        $this->line = $line;
        $this->row = $row;
    }
}
