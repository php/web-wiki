<?php
/**
 * Plugin link to a php bug id
 *
 * @license    LGPL 3 (http://www.gnu.org/licenses/lgpl.html)
 * @author     Lukas Kahwe Smith <smith@pooteeweet.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class  syntax_plugin_phpbugid extends DokuWiki_Syntax_Plugin {


    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Lukas Kahwe Smith',
        'email'  => 'smith@pooteeweet.org',
        'date'   => '2008-06-20',
        'name'   => 'PHP Bug id',
        'desc'   => 'Add a short syntax to link to php bugs',
        'url'    => 'http://cvs.php.net/viewvc.cgi/php-wiki-web/dokuwiki/lib/plugins/phpbugid/syntax.php?view=markup',
      );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    // Just before build in links
    function getSort(){ return 299; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[bugid\@\d+[^\]]*\]\]',$mode,'plugin_phpbugid');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = substr($match,8,-2); //strip [[bugid@ from start and ]] from end
        $match = explode("|",$match);
        return $match;
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $text=$this->_buglink($renderer, $data[0], $data[1]);
            $renderer->doc .= $text;
            return true;
        }
        return false;
    }


    function _buglink(&$renderer, $bugid, $name = NULL) {
        global $conf;
        if (!isset($name)) {
            $name = 'bug #'.$bugid;
        }

        $url = 'http://bugs.php.net/bug.php?id='.$bugid;

//        $name = $renderer->_xmlEntities($renderer->_getLinkTitle($name, $url, $isImage));
        $name = $renderer->_getLinkTitle($name, $url, $isImage);

        $class='urlextern';
        $link['target'] = $conf['target']['wiki'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = $url;
        $link['name']   = $name;
        $link['title']  = $renderer->_xmlEntities($name);

        //output formatted
        return $renderer->_formatLink($link);
    }

}
?>
