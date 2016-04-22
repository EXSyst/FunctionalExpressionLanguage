<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Library;

abstract class Module
{
    abstract public function getName(): string;

    /**
     * @return Operator[]
     */
    public function getOperators(): array
    {
        return [];
    }

    /**
     * @return Function[]
     */
    public function getFunctions(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getProvides(): array
    {
        return [];
    }
}
