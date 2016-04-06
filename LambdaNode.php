<?php
namespace EXSyst\Component\FunctionalExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\Compiler;

class LambdaNode extends Node
{
    private $argumentMap;

    public function __construct($arguments, $uses, $body)
    {
        parent::__construct([ 'arguments' => $arguments, 'body' => $body ], [ 'uses' => $uses ]);
        $this->argumentMap = [ ];
        foreach ($arguments as $argument) {
            $this->argumentMap[] = $argument->attributes['name'];
        }
    }

    public function compile(Compiler $compiler)
    {
        $compiler->raw('function (');
        $first = true;
        foreach ($this->nodes['arguments'] as $argument) {
            if ($first) {
                $first = false;
            } else {
                $compiler->raw(', ');
            }
            $compiler->compile($argument);
        }
        $compiler->raw(')');
        $uses = $this->attributes['uses'];
        if (count($uses)) {
            $compiler->raw(' use (');
            $first = true;
            foreach ($uses as $use) {
                if ($first) {
                    $first = false;
                } else {
                    $compiler->raw(', ');
                }
                $compiler->raw('$' . $use);
            }
            $compiler->raw(')');
        }
        $compiler->raw(' { return ');
        $compiler->compile($this->nodes['body']);
        $compiler->raw('; }');
    }

    public function evaluate($functions, $values)
    {
        return function () use ($functions, $values) {
            $innerValues = $values;
            $arguments = func_get_args();
            foreach ($this->argumentMap as $i => $key) {
                if (isset($arguments[$i])) {
                    $innerValues[$key] = $arguments[$i];
                } else {
                    $innerValues[$key] = null;
                }
            }
            return $this->nodes['body']->evaluate($functions, $innerValues);
        };
    }
}
