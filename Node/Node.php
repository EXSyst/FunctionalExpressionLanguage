<?php

namespace EXSyst\Component\FunctionalExpressionLanguage\Node;

use EXSyst\Component\FunctionalExpressionLanguage\Visitor\NodeVisitor;
use EXSyst\Component\FunctionalExpressionLanguage\Visitor\VisitorState;

abstract class Node
{
    abstract public function accept(NodeVisitor $visitor, VisitorState $state);
}
