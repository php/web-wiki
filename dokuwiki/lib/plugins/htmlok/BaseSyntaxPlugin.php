<?php
/**
 * DokuWiki Plugin htmlok (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 * @author  Elan Ruusamäe <glen@delfi.ee>
 */

namespace dokuwiki\plugin\htmlok;

use Doku_Handler;
use Doku_Renderer;

abstract class BaseSyntaxPlugin extends \dokuwiki\Extension\SyntaxPlugin
{
    /** @var string */
    protected $type = 'protected';
    /** @var string */
    protected $ptype;
    /** @var int */
    protected $sort;
    /** @var string */
    protected $tag;
    /** @var string */
    protected $mode;
    /** @var string|null */
    protected $class;

    public function getType(): string
    {
        return $this->type;
    }

    public function getPType(): string
    {
        return $this->ptype;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern("<{$this->tag}>(?=.*?</{$this->tag}>)", $mode, $this->mode);
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern("</{$this->tag}>", $this->mode);
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                return [$state, $match];
            case DOKU_LEXER_UNMATCHED:
                return [$state, $match];
            case DOKU_LEXER_EXIT:
                return [$state, ''];
        }

        return [];
    }

    public function render($mode, Doku_Renderer $renderer, $data): bool
    {
        if ($mode !== 'xhtml') {
            return false;
        }

        [$state, $match] = $data;
        switch ($state) {
            case DOKU_LEXER_ENTER:
                if ($this->ptype === 'block') {
                    $renderer->doc .= '<div class="' . $this->class . '">' . DOKU_LF;
                }
                break;
            case DOKU_LEXER_UNMATCHED:
                $renderer->doc .= $this->renderMatch($match);
                break;
            case DOKU_LEXER_EXIT:
                if ($this->ptype === 'block') {
                    $renderer->doc .= '</div>' . DOKU_LF;
                }
                break;
        }

        return true;
    }

    abstract protected function renderMatch(string $match): string;

    /**
     * Execute PHP code if allowed
     *
     * @param string $text PHP code that is either executed or printed
     * @param string $wrapper html element to wrap result if $conf['phpok'] is okff
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Elan Ruusamäe <glen@delfi.ee>
     */
    protected function php(string $text, string $wrapper = 'code'): string
    {
        if ($this->getConf('phpok')) {
            ob_start();
            eval($text);
            $contents= ob_get_contents();
            ob_end_clean();
            return $contents;
        }

        return p_xhtml_cached_geshi($text, 'php', $wrapper);
    }

    /**
     * Output block level PHP code
     *
     * If 'phpok' is true this should evaluate the given code and append the result
     * to document.
     *
     * @param string $text The PHP code
     */
    protected function phpblock(string $text): string
    {
        return $this->php($text, 'pre');
    }

    /**
     * Insert HTML if allowed
     *
     * @param string $text html text
     * @param string $wrapper html element to wrap result if 'htmlok' is ok.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Elan Ruusamäe <glen@delfi.ee>
     */
    protected function html(string $text, string $wrapper = 'code'): string
    {
        if ($this->getConf('htmlok')) {
            return $text;
        }

        return p_xhtml_cached_geshi($text, 'html4strict', $wrapper);
    }

    /**
     * Output raw block-level HTML
     *
     * If $conf['htmlok'] is true this should add the code as is to $doc
     *
     * @param string $text The HTML
     */
    protected function htmlblock(string $text): string
    {
        return $this->html($text, 'pre');
    }
}
