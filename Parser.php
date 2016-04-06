<?php
namespace EXSyst\Component\FunctionalExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Parser as BaseParser;
use Symfony\Component\ExpressionLanguage\Token;
use Symfony\Component\ExpressionLanguage\TokenStream;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\ExpressionLanguage\Node\NameNode;

/** {@inheritdoc} */
class Parser extends BaseParser
{
    private static $streamProperty;
    private static $streamPositionProperty;
    private static $namesProperty;

    public function __construct(array $functions)
    {
        self::initProperties();
        parent::__construct($functions);
    }

    private static function initProperties()
    {
        if (!self::$streamProperty) {
            self::$streamProperty = PatchHelper::getProperty(parent::class, 'stream');
        }
        if (!self::$streamPositionProperty) {
            self::$streamPositionProperty = PatchHelper::getProperty(TokenStream::class, 'position');
        }
        if (!self::$namesProperty) {
            self::$namesProperty = PatchHelper::getProperty(parent::class, 'names');
        }
    }

    public function parseExpression($precedence = 0)
    {
        if ($precedence <= 0) {
            $lambda = $this->tryParseLambda();
            if ($lambda !== null) {
                return $lambda;
            }
        }

        return parent::parseExpression($precedence);
    }

    private static function sync(TokenStream $target, TokenStream $source)
    {
        self::$streamPositionProperty->setValue($target, self::$streamPositionProperty->getValue($source));
        $target->current = $source->current;
    }

    private function tryParseLambda()
    {
        $originalStream = self::$streamProperty->getValue($this);
        $stream = clone $originalStream;
        $names = self::$namesProperty->getValue($this);
        $token = $stream->current;
        $arguments = [];
        if ($token->test(Token::PUNCTUATION_TYPE, '(')) {
            $stream->next();
            $token = $stream->current;
            if ($token->test(Token::PUNCTUATION_TYPE, ')')) {
                $stream->next();
                $token = $stream->current;
            } else {
                for (; ; ) {
                    if (!$token->test(Token::NAME_TYPE)) {
                        return;
                    }
                    $arguments[] = $token;
                    $stream->next();
                    $token = $stream->current;
                    if ($token->test(Token::PUNCTUATION_TYPE, ')')) {
                        $stream->next();
                        $token = $stream->current;
                        break;
                    } elseif (!$token->test(Token::PUNCTUATION_TYPE, ',')) {
                        return;
                    }
                    $stream->next();
                    $token = $stream->current;
                }
            }
        } elseif ($token->test(Token::NAME_TYPE)) {
            $arguments[] = $token;
            $stream->next();
            $token = $stream->current;
        } elseif ($token->test(Token::OPERATOR_TYPE, '=>')) {
            $stream->next();
            self::sync($originalStream, $stream);
            return $this->lambda($arguments, $names);
        } else {
            return;
        }
        if ($token->test(Token::OPERATOR_TYPE, '=>')) {
            $stream->next();
            self::sync($originalStream, $stream);
            return $this->lambda($arguments, $names);
        }
    }

    private function lambda($arguments, $outerNames)
    {
        $innerNames = $outerNames;
        foreach ($arguments as &$argument) {
            if (in_array($argument->value, $outerNames, true)) {
                throw new SyntaxError(sprintf('Lambda argument "%s" is not valid', $argument->value), $argument->cursor);
            }
            $innerNames[] = $argument->value;
            $argument = new NameNode($argument->value);
        }
        $uses = [ ];
        foreach ($outerNames as $expr => $name) {
            if (is_int($expr)) {
                $expr = $name;
            }
            $expr = substr($expr, 0, strspn($expr, "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz\x7f\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa0\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xad\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff"));
            $uses[$expr] = true;
        }
        $uses = array_keys($uses);
        self::$namesProperty->setValue($this, $innerNames);
        try {
            return new LambdaNode($arguments, $uses, $this->parseExpression());
        } finally {
            self::$namesProperty->setValue($this, $outerNames);
        }
    }
}
