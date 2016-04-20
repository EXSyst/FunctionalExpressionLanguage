<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;

abstract class Node
{
    abstract public function accept(NodeVisitor $visitor);
}
