<?php
/**
 * Blockquote Plugin
 *
 * Allows correctly formatted blockquotes
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Gina Haeussge <osd@foosel.net>
 * @author  Anika Henke <anika@selfthinker.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_blockquote_blockquote extends DokuWiki_Syntax_Plugin {

    function getType() {
        return 'container';
    }

    function getPType() {
        return 'stack';
    }

    function getAllowedTypes() {
        return array (
            'container',
            'substition',
            'protected',
            'disabled',
            'formatting',
            'paragraphs'
        );
    }

    function getSort() {
        return 123;
    }

    function accepts($mode) {
        if ($mode == substr(get_class($this), 7))
            return true;
        return parent :: accepts($mode);
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<blockquote.*?>(?=.*?</blockquote>)', $mode, 'plugin_blockquote_blockquote');
        $this->Lexer->addEntryPattern('<QUOTE.*?>(?=.*?</QUOTE>)', $mode, 'plugin_blockquote_blockquote');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</blockquote>', 'plugin_blockquote_blockquote');
        $this->Lexer->addExitPattern('</QUOTE>', 'plugin_blockquote_blockquote');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {

            case DOKU_LEXER_ENTER :
                $source = trim(substr($match,strpos($match,' '),-1));
                return array (
                    $state,
                    $source
                );

            case DOKU_LEXER_UNMATCHED :
                return array (
                    $state,
                    $match
                );

            default :
                return array (
                    $state,
                    ''
                );
        }
    }

    function render($mode, Doku_Renderer $renderer, $indata) {
        if ($mode == 'xhtml') {

            list ($state, $data) = $indata;

            switch ($state) {
                case DOKU_LEXER_ENTER :

                    $pluginClass = ($this->getConf('addStyling')) ? 'blockquote-plugin' : '';
                    $attr = '';
                    if (($data && strlen($data) > 0) && !plugin_isdisabled('wrap')) {
                        // get attributes from wrap helper plugin (if installed)
                        $wrap =& plugin_load('helper', 'wrap');
                        $attr = $wrap->buildAttributes($data, $pluginClass);
                    } else if ($pluginClass) {
                        $attr = 'class="'.$pluginClass.'"';
                    }

                    $renderer->doc .= '<blockquote '.$attr.'>';
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $renderer->doc .= $renderer->_xmlEntities($data);
                    break;

                case DOKU_LEXER_EXIT :
                    $renderer->doc .= "\n</blockquote>";
                    break;
            }
            return true;
        }

        // unsupported $mode
        return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
