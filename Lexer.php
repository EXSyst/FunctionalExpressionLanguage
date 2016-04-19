<?php

namespace EXSyst\Component\FunctionalExpressionLanguage;

use EXSyst\Component\IO\Exception\UnderflowException;
use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\FunctionalExpressionLanguage\ParserInterface;

class Lexer
{
    const BASE_MASK = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz\x7f\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa0\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xad\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff';
    const NUMBERS_MASK = '0123456789';
    const PUNCTUATION_MASK = '()[]{},;:.';
    const QUOTE_MASK = '\'"';
    const WHITE_SPACE_MASK = "\011\013\014 ";
    const EOL_MASK = "\r\n";

    private $parser;
    private $cursor = 0;
    private $row = 0;
    private $line = 0;

    /**
     * @param CDataReader|string $source
     * @param Parser             $parser the Parser to call when a new token is discovered
     */
    public function __construct($source, ParserInterface $parser)
    {
        $this->parser = $parser;

        if (!$source instanceof CDataReader) {
            $source = CDataReader::fromString($source);
        }

        while (!$source->isFullyConsumed()) {
            if ($eol = $source->eatAny(["\n\r", "\r\n", "\n", "\r"])) {
                $this->sendToken(TokenType::EOL, $eol);
            } elseif ($commentTag = $source->eatAny(['//', '--', '#'])) {
                $comment = $commentTag.$source->eatCSpan(self::EOL_MASK);
                $this->sendToken(TokenType::COMMENT, $comment);
            } elseif ($source->eat('/*')) {
                $comment = '/*';
                try {
                    while (true) {
                        $comment .= $source->eatCSpan('*');
                        if ($source->eat('*/')) {
                            $comment .= '*/';
                            $this->sendToken(TokenType::COMMENT, $comment);

                            break;
                        } else {
                            $comment .= $source->read(1);
                        }
                    }
                } catch (UnderflowException $exception) {
                    throw new \RuntimeException('Unclosed comment');
                }
            } elseif ($spaces = $source->eatSpan(self::WHITE_SPACE_MASK)) {
                $this->sendToken(TokenType::WHITE_SPACE, $spaces);
            } elseif ($punctuation = $source->eatSpan(self::PUNCTUATION_MASK, 1)) {
                $this->sendToken(TokenType::PUNCTUATION, $punctuation);
            } elseif ($quote = $source->eatSpan(self::QUOTE_MASK, 1)) { // strings
                $this->eatString($source, $quote);
            } elseif ($number = $source->eatSpan(self::NUMBERS_MASK)) { // numbers
                if ($source->eat('.')) {
                    $number .= '.';
                }
                $number .= $source->eatSpan(self::BASE_MASK.self::NUMBERS_MASK);

                $this->sendToken(TokenType::LITERAL, $number);
            } elseif (!$this->eatName($source) && !$this->eatSymbol($source)) {
                throw new \Exception(sprintf('Unexpected token "%s"', $source->eatToFullConsumption()));
            }
        }

        $this->sendToken(TokenType::EOF);
    }

    /**
     * Eats a literal such as 'This is a string.' or 'this is human\'s'.
     *
     * @param string $quote either ' or ".
     */
    private function eatString(CDataReader $source, string $quote)
    {
        $value = '';
        try {
            while (true) {
                $value .= $source->eatCSpan('\\'.$quote);

                $next = $source->read(1);
                if ($next === $quote) {
                    $value = $quote.$value.$quote.$source->eatSpan(self::BASE_MASK.self::NUMBERS_MASK);

                    return $this->sendToken(TokenType::LITERAL, $value);
                } else {
                    $value .= $next;
                    $value .= $source->read(1);
                }
            }
        } catch (UnderflowException $exception) {
            throw new \RuntimeException(sprintf('Unterminated string literal (line %d, row %d)', $this->line, $this->row));
        }
    }

    /**
     * Eats a symbol (!=, and all non previously treated caracters).
     */
    private function eatSymbol(CDataReader $source): bool
    {
        $symbol = $source->eatCSpan(self::BASE_MASK.self::NUMBERS_MASK.self::PUNCTUATION_MASK.self::QUOTE_MASK.CDataReader::WHITE_SPACE_MASK);

        if ($symbol) {
            $this->sendToken(TokenType::SYMBOL, $symbol);
        }

        return $symbol;
    }

    /**
     * Eats a name (may be a variable or a function call).
     */
    private function eatName(CDataReader $source): bool
    {
        if (!$name = $source->eatSpan(self::BASE_MASK)) {
            return false;
        }
        $name .= $source->eatSpan(self::BASE_MASK.self::NUMBERS_MASK);
        $this->sendToken(TokenType::NAME, $name);

        return true;
    }

    private function sendToken(int $type, string $value = null)
    {
        $this->parser->accept(new Token($type, $value, $this->cursor, $this->line, $this->row));
        $this->moveCursor($value);
    }

    private function moveCursor(string $value = null)
    {
        $this->cursor += strlen($value);
        $this->row += strlen($value);

        if ($lines = preg_match_all("/\r\n?|\n\r?/", $value)) {
            $this->line += $lines;
            $this->row = strlen($value) - max(strrpos($value, "\r"), strrpos($value, "\n")) - 1;
        }
    }
}
