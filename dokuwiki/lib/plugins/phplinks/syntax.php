<?php

/**
 * Shortcut syntax for links to PHP mailing lists and bug tracker
 *
 * @license LGPL 3 (http://www.gnu.org/licenses/lgpl.html)
 * @author  Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author  Christoph Michael Becker <cmb@php.net>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class.
 */
class syntax_plugin_phplinks extends DokuWiki_Syntax_Plugin
{
    /**
     * Returns the type of syntax this plugin defines.
     *
     * @return string
     */
    function getType()
    {
        return 'substition';
    }

    /**
     * Returns a number used to determine in which order modes are added.
     *
     * @return int
     *
     * @link https://www.dokuwiki.org/devel:parser:getsort_list
     */
    function getSort()
    {
        return 299;
    }

    /**
     * Registers the regular expressions needed to match the special syntax.
     *
     * @param string $mode
     *
     * @return void
     */
    function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '\[\[[-a-z]+\@\d+[^\]]*\]\]', $mode, 'plugin_phplinks'
        );
    }

    /**
     * Prepares the matched syntax for use in the renderer.
     *
     * @param string       $match
     * @param int          $state
     * @param int          $pos
     * @param Doku_Handler $handler
     *
     * @return void
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {
        preg_match('/\[\[([-a-z]+)\@(\d+)\|?([^\]]*)\]\]/', $match, $matches);
        if ($matches[1] == 'bugid') {
            $name = $matches[3] ? $matches[3] : 'bug #' . $matches[2];
            $url = 'http://bugs.php.net/bug.php?id=' . $matches[2];
        } else {
            $name = $matches[3] ? $matches[3] : 'ml#' . $matches[2];
            $url = 'http://marc.info/?l=' . $matches[1] . '&m=' . $matches[2];
        }
        return array($name, $url);
    }

    /**
     * Renders the content.
     *
     * @param string        $mode
     * @param Doku_Renderer $renderer
     * @param mixed         $data
     *
     * @return void
     */
    function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode == 'xhtml') {
            $renderer->doc .= $this->_renderLink($renderer, $data[0], $data[1]);
            return true;
        }
        return false;
    }

    /**
     * Renders a link.
     *
     * @param Doku_Renderer $renderer
     * @param string        $name
     * @param string        $url
     *
     * @return string
     *
     * @global array $conf
     */
    private function _renderLink(Doku_Renderer $renderer, $name, $url)
    {
        global $conf;

        $link = array(
            'target' => $conf['target']['wiki'],
            'style'  => '',
            'pre'    => '',
            'suf'    => '',
            'more'   => '',
            'class'  => 'urlextern',
            'url'    => $url,
            'name'   => $name,
            'title'  => $renderer->_xmlEntities($name)
        );
        return $renderer->_formatLink($link);
    }
}

?>
