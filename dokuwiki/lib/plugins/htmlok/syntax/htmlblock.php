<?php
/**
 * DokuWiki Plugin htmlok (Syntax Component)
 *
 * @license GPL 2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 * @author  Elan Ruusamäe <glen@delfi.ee>
 */

use dokuwiki\plugin\htmlok\BaseSyntaxPlugin;

class syntax_plugin_htmlok_htmlblock extends BaseSyntaxPlugin
{
    protected $ptype = 'block';
    protected $sort = 190;
    protected $tag = 'HTML';
    protected $mode = 'plugin_htmlok_htmlblock';
    protected $class = 'htmlblock';

    protected function renderMatch(string $match): string
    {
        return $this->htmlblock($match);
    }
}
