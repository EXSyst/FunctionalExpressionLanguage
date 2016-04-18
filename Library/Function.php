<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Library;

use EXSyst\Component\FunctionalExpressionLanguage\Node\NodeInterface;

final class Function
{
    private $name;

    /**
     * @param string             $name      the name of the Function
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
