<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\FunctionalExpressionLanguage\Token;

class TokenIterator
{
    private $position = 0;
    private $tokens = [];

    public function append(Token $token)
    {
        $this->tokens[] = $token;
    }

    public function rewind($position = 0) {
        $this->position = $position;
    }

    public function current() {
        return $this->tokens[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->tokens[$this->position]);
    }
}
