<?php

/**
 * DokuWiki Plugin htmlok (Syntax Component)
 *
 * @license GPL 2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 * @author  Elan Ruusamäe <glen@delfi.ee>
 */

use dokuwiki\plugin\htmlok\BaseSyntaxPlugin;

class syntax_plugin_htmlok_phpblock extends BaseSyntaxPlugin
{
    protected $ptype = 'block';
    protected $sort = 180;
    protected $tag = 'PHP';
    protected $mode = 'plugin_htmlok_phpblock';
    protected $class = 'phpok';

    protected function renderMatch(string $match): string
    {
        return $this->phpblock($match);
    }
}
