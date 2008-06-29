<?php
/**
 * Plugin link to a php mailinglist
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
class  syntax_plugin_phpml extends DokuWiki_Syntax_Plugin {


    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Lukas Kahwe Smith',
        'email'  => 'smith@pooteeweet.org',
        'date'   => '2008-06-20',
        'name'   => 'PHP ML',
        'desc'   => 'Add a short syntax to link to php mailinglists',
        'url'    => 'http://cvs.php.net/viewvc.cgi/php-wiki-web/dokuwiki/lib/plugins/phpml/syntax.php?view=markup',
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
        $this->Lexer->addSpecialPattern('\[\[[-a-z]+\@\d+[^\]]*\]\]',$mode,'plugin_phpml');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        if (preg_match('/\[\[([-a-z]+)\@(\d+)\|?([^\]]*)\]\]/', $match, $matches)) {
            array_shift($matches);
            if (count($matches) < 2) {
                $matches[1] = null;
            }
            if (count($matches) < 3) {
                $matches[2] = null;
            }
        } else {
            $matches - array(null, null, null);
        }

        return $matches;
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $text=$this->_mllink($renderer, $data[0], $data[1], $data[2]);
            $renderer->doc .= $text;
            return true;
        }
        return false;
    }


    function _mllink(&$renderer, $ml, $msgid, $name = NULL) {
        global $conf;
        if (!isset($name) || $name === '') {
            $name = 'ml#'.$msgid;
        }

        $url = 'http://marc.info/?l='.$ml.'&m='.$msgid;

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
