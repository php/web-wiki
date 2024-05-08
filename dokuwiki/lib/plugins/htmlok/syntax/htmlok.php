<?php
/**
 * DokuWiki Plugin htmlok (Syntax Component)
 *
 * @license GPL 2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 * @author  Elan Ruusamäe <glen@delfi.ee>
 */

use dokuwiki\plugin\htmlok\BaseSyntaxPlugin;

class syntax_plugin_htmlok_htmlok extends BaseSyntaxPlugin
{
    protected $ptype = 'normal';
    protected $sort = 190;
    protected $tag = 'html';
    protected $mode = 'plugin_htmlok_htmlok';

    protected function renderMatch(string $match): string
    {
        return $this->html($match);
    }
}
