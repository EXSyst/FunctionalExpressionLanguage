<?php
namespace EXSyst\Component\FunctionalExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

use Symfony\Component\ExpressionLanguage\Lexer as BaseLexer;
use Symfony\Component\ExpressionLanguage\Parser as BaseParser;

/** {@inheritdoc} */
class FunctionalExpressionLanguage extends ExpressionLanguage
{
    /** {@inheritdoc} */
    public function __construct(ParserCacheInterface $cache = null, array $providers = array())
    {
        parent::__construct($cache, $providers);
        $this->initLexerAndParser();
    }

    protected function initLexerAndParser()
    {
        $this->setLexer(new Lexer());
        $this->setParser(new Parser($this->functions));
    }

    protected function setLexer(BaseLexer $lexer = null)
    {
        PatchHelper::set($this, parent::class, 'lexer', $lexer);

        return $this;
    }

    protected function setParser(BaseParser $parser = null)
    {
        PatchHelper::set($this, parent::class, 'parser', $parser);

        return $this;
    }
}
