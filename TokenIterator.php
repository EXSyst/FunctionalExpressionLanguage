<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;
use EXSyst\Component\FunctionalExpressionLanguage\Token;

class TokenIterator implements \SeekableIterator
{
    private $position = 0;
    private $tokens = [];

    public function append(Token $token)
    {
        $this->tokens[] = $token;
    }

    public function seek($position) {
      if (!isset($this->tokens[$position])) {
          throw new \OutOfBoundsException("invalid seek position ($position)");
      }

      $this->position = $position;
    }

    public function rewind() {
        $this->position = 0;
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
