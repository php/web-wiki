<?php
/**
 * Hidden Comment Plugin: allows hidden comments in the wiki source
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_comment extends DokuWiki_Syntax_Plugin {
 
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Gina Häußge, Michael Klier, Esther Brunner',
            'email'  => 'dokuwiki@chimeric.de',
            'date'   => '2008-02-11',
            'name'   => 'Hidden Comment Plugin',
            'desc'   => 'allows hidden comments in the wiki source',
            'url'    => 'http://wiki.splitbrain.org/plugin:comment',
        );
    }
 
    function getType(){ return 'substition'; }
    function getSort(){ return 321; }
    
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern("/\*.*?\*/", $mode, 'plugin_comment');
      $this->Lexer->addSpecialPattern("//.*?$", $mode, 'plugin_comment');
    }
    
    function handle($match, $state, $pos, &$handler){ return ''; }            
    function render($mode, &$renderer, $data) { return true; }
     
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
