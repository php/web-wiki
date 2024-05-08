<?php
/**
 * DokuWiki Plugin htmlok (Syntax Component)
 *
 * @license GPL 2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 * @author  Elan Ruusamäe <glen@delfi.ee>
 */

use dokuwiki\plugin\htmlok\BaseSyntaxPlugin;

class syntax_plugin_htmlok_phpok extends BaseSyntaxPlugin
{
    protected $ptype = 'normal';
    protected $sort = 180;
    protected $tag = 'php';
    protected $mode = 'plugin_htmlok_phpok';

    protected function renderMatch(string $match): string
    {
        return $this->php($match);
    }
}
