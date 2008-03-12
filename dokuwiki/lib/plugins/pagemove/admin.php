<?php
/**
 * Plugin : Pagemove
 * Version : 0.9.15 (20/07/2007)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gary Owen <gary@isection.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
require_once(DOKU_INC.'inc/search.php');

//print '<pre>';print_r($this->lang);print '</pre>';
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_pagemove extends DokuWiki_Admin_Plugin {

    var $show_form = true;
    var $have_rights = true;
    var $locked_files = array();
    var $errors = array();
    var $opts = array();
    var $text = '';

    /**
     * function constructor
     */
    function admin_plugin_pagemove(){
      // enable direct access to language strings
      $this->setupLocale();
    }

    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'Gary Owen',
        'email'  => 'gary@isection.co.uk',
        'date'   => '2007-02-12',
        'name'   => 'PageMove',
        'desc'   => $this->lang['desc'],
        'url'    => 'http://wiki.splitbrain.org/plugin:pagemove',
      );
    }

    /**
     * Only show the menu text for pages we can move or rename.
     */
    function getMenuText() {
      global $INFO;
      global $ID;
      global $conf;

      if(!$INFO['exists'])
        return $this->lang['menu'].' ('.$this->lang['pm_notexist'].')';
      elseif ( $ID == $conf['start'])
        return $this->lang['menu'].' ('.$this->lang['pm_notstart'].')';
      elseif ( !$INFO['writable'] )
        return $this->lang['menu'].' ('.$this->lang['pm_notwrite'].')';
      else
        return $this->lang['menu'];
    }

    /**
     * handle user request
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function handle() {

      global $conf;
      global $lang;
      global $ID;
      global $INFO;
      global $ACT;
      global $opts;

      // extract namespace and document name from ID
      $opts['ns']   = getNS($ID);
      $opts['name'] = noNS($ID);

      // Check we have rights to move this document
      if ( !$INFO['exists']) {
        $this->have_rights = false;
        $this->errors[] = $this->lang['pm_notexist'];
        return;
      }
      if ( $ID == $conf['start']) {
        $this->have_rights = false;
        $this->errors[] = $this->lang['pm_notstart'];
      }
      if ( auth_quickaclcheck($ID) < AUTH_EDIT ) {
        $this->have_rights = false;
        $this->errors[] = $this->lang['pm_norights'];
      }

      // Check file is not locked
      if (checklock($ID))
        $this->locked_files[] = $ID;

      // Check we have edit rights on the backlinks and they are not locked
      $backlinks = array();
      $this->_pm_search($backlinks,$conf['datadir'],'_pm_search_backlinks',$opts);

      foreach($backlinks as $backlink =>$links){
        if ( auth_quickaclcheck($backlink) < AUTH_EDIT )
          $this->have_rights = false;
        if (checklock($backlink))
          $this->locked_files[] = $backlink;
      }

      // Validate the form data if set
      if ( isset($_REQUEST['ns']) or isset($_REQUEST['pagename']) ) {
        if ( $_REQUEST['nsr'] == '<new>' ) {
          //New namespace
          $opts['newns'] = $_REQUEST['newns'];
          //Check that the namespace is valid
          if ( cleanID($opts['newns']) == '' ) {
            $this->errors[] = $this->lang['pm_badns'];
          }
        } else {
          $opts['newns'] = ($_REQUEST['ns'] == ':' ? '' : $_REQUEST['ns']);
        }

        //Check that the pagename is valid
        $opts['newname'] = $_REQUEST['pagename'];
        if ( cleanID($opts['newname']) == '' ) {
          $this->errors[] = $this->lang['pm_badname'];
        }
        
        //Assemble fill document name and path
        $opts['new_id'] = cleanID($opts['newns'].':'.$opts['newname']);
        $opts['new_path'] = wikiFN($opts['new_id']);

        //Has the document name and/or namespace changed?
        if ( $opts['newns'] == $opts['ns'] and $opts['newname'] == $opts['name'] ) {
          $this->errors[] = $this->lang['pm_nochange'];
        }
        //Check the page does not already exist
        elseif ( @file_exists($opts['new_path']) ) {
          $this->errors[] = sprintf($this->lang['pm_existing'], $opts['newname'],
           ($opts['newns'] == '' ? $this->lang['pm_root'] : $opts['newns']));
        }

        //If there are no errors we can do the move/rename
        if ( count($this->errors) == 0 ) {
          //Enclosing brackets definitions for links and media

          //Open the old document and change forward links
          lock($ID);
          $this->text = io_readFile(wikiFN($ID),True);

          //Get an array of forward links from the document
          $forward = $this->_pm_getforwardlinks($ID);

          //Change the forward links
          foreach($forward as $lnk => $lid){
            //Get namespace of target document
            $tns = getNS($lid);
            $tname = noNS($lid);
            //Form new document id for the target
            $matches = array();
            if ( $tns == $opts['newns'] ) { //Document is in same namespace as target
              $this->_pm_updatelinks($this->text, array($lnk => $tname));
            } elseif ( preg_match('#^'.$opts['newns'].':(.*)$#', $tns, $matches) ) { //Target is in a sub-namespace
              $this->_pm_updatelinks($this->text, array($lnk => '.:'.$matches[1].':'.$tname));
            } elseif ( $tns == "" ) { //Target is in root namespace
              $this->_pm_updatelinks($this->text, array($lnk => ':'.$lid ));
            } else {
              $this->_pm_updatelinks($this->text, array($lnk => $lid ));
            }
          }

          if ( $opts['ns'] != $opts['newns'] ) {  //Change media links when moving namespace
            //Get an array of media links from the document
            $media = $this->_pm_getmedialinks($ID);

            //Change the media links
            foreach($media as $lnk => $lid){
              //Get namespace of target document
              $tns = getNS($lid);
              $tname = noNS($lid);
              //Form new document id for the target
              $matches = array();
              if ( $tns == $opts['newns'] ) { //Document is in same namespace as target
                $this->_pm_updatemedialinks($this->text, $lnk, $tname );
              } elseif ( preg_match('#^'.$opts['newns'].':(.*)$#', $tns, $matches) ) { //Target is in a sub-namespace
                $this->_pm_updatemedialinks($this->text, $lnk, '.:'.$matches[1].':'.$tname );
              } elseif ( $tns == "" ) { //Target is in root namespace
                $this->_pm_updatemedialinks($this->text, $lnk, ':'.$lid );
              } else {
                $this->_pm_updatemedialinks($this->text, $lnk, $lid );
              }
            }
          }

          //Move the Subscriptions & Indexes
          $this->_pm_movemeta('metadir', '/^'.$opts['name'].'\.\w*?$/', $opts);

          //Save the updated document in its new location.
          if ($opts['ns'] == $opts['newns']) {
            $lang_key = 'pm_renamed';
          } elseif ( $opts['name'] == $opts['newname'] ) {
            $lang_key = 'pm_moved';
          } else {
            $lang_key = 'pm_move_rename';
          }
          saveWikiText($opts['new_id'], $this->text,
            sprintf($this->lang[$lang_key], $ID, $opts['new_id']));

          //Delete the orginal file.
          //saveWikiText($ID, '', $this->lang['pm_movedto'].$opts['new_id']);
          if (@file_exists(wikiFN($opts['new_id']))) @unlink(wikiFN($ID));

          //Loop through backlinks
          foreach($backlinks as $backlink => $links){
            $this->_pm_updatebacklinks($backlink, $links, $opts, $brackets);
          }

          //Move the old revisions
          $this->_pm_movemeta('olddir', '/^'.$opts['name'].'\.[0-9]{10}\.txt(\.gz)?$/', $opts);

          //Set things up to display the new page.
          io_saveFile($conf['cachedir'].'/purgefile',time());
          $ID = $opts['new_id'];
          $ACT = 'show';
          $INFO = pageinfo();
          $this->show_form = false;
        }
      }
    }

    /**
     * output appropriate html
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function html() {
      global $lang;

      ptln('<!-- Pagemove Plugin start -->');
      if ( $this->show_form ) {
        ptln( $this->locale_xhtml('pagemove') );
        //We didn't get here from submit.
        if ( $this->have_rights and count($this->locked_files) == 0 ) {
          $this->_pm_form($this->errors);
        } else {
          ptln( '<p><strong>' );
          if ( !$this->have_rights ) {
            ptln( $this->errors[0].'<br>' );
          }
          $c = count($this->locked_files);
          if ( $c == 1 ) {
            ptln( $this->lang['pm_filelocked'].$this->locked_files[0].'<br>'.$this->lang['pm_tryagain'] );
          } elseif ( $c > 1 ) {
            ptln( $this->lang['pm_fileslocked'] );
            for ( $i = 0 ; $i < $c ; $i++ )
              ptln ( ($i > 0 ? ', ' : '').$this->locked_files[$i] );
            ptln( '<br>'.$this->lang['pm_tryagain'] );
          }
          ptln ( '</strong></p>' );
        }
      } else {
        // display the moved/renamed page
        ptln( $this->render($this->text) );
      }
      ptln('<!-- Pagemove Plugin end -->');
    }

    /**
     * show the move and/or rename a page form
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_form($errors)
    {
      global $ID;
      global $lang;
      global $conf;
      global $opts;

      ptln('  <div align="center">');
      ptln('  <script language="Javascript">');
      ptln('      function setradio( group, choice ) {');
      ptln('        for ( i = 0 ; i < group.length ; i++ ) {');
      ptln('          if ( group[i].value == choice )');
      ptln('            group[i].checked = true;');
      ptln('        }');
      ptln('      }');
      ptln('  </script>');
      ptln('  <form name="frm" action="'.wl($ID).'" method="post">');
      ptln('  <fieldset>');
       // output hidden values to ensure dokuwiki will return back to this plugin
      ptln('    <input type="hidden" name="do"   value="admin" />');
      ptln('    <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
      ptln('    <input type="hidden" name="id" value="'.$ID.'" />');
      ptln('    <table border="0">');

      //Show any errors
        if (count($this->errors) > 0) {
          ptln ('<tr><td bgcolor="red" colspan="3">');
          foreach($this->errors as $error)
            ptln ($error.'<br>');
          ptln ('</td></tr>');
        }
        //create a list of namespaces
        $namesp = array( 0 => '' );     //Include root
        search($namesp,$conf['datadir'],'search_namespaces',$opts);
        sort($namesp);

        ptln( '      <tr><td align="right" rowspan="2" nowrap><label><span>'.$this->lang['pm_targetns'].'</span></label></td>');
        ptln( '        <td width="25"><input type="radio" name="nsr" value="<old>" '.($_REQUEST['nsr']!= '<new>' ? 'CHECKED' : '').'></td>');
        ptln( '        <td><select name="ns">');
        foreach($namesp as $row) {
          if ( auth_quickaclcheck($row['id'].':*') >= AUTH_CREATE or $row['id'] == $opts['ns'] ) {
            ptln ( '          <option value="'.
                    ($row['id'] ? $row['id'] : ':').
                    ($_REQUEST['ns'] ?
                      (($row['id'] ? $row['id'] : ":") == $_REQUEST['ns'] ? '" SELECTED>' : '">') :
                      ($row['id'] == $opts['ns'] ? '" SELECTED>' : '">') ).
                    ($row['id'] ? $row['id'].':' : ": ".$this->lang['pm_root']).
                    ($row['id'] == $opts['ns'] ? ' '.$this->lang['pm_current'] : '').
                    "</option>" );
          }
        }
        ptln( "        </select></td>\n      </tr><tr>");

        ptln( '        <td width="25"><input type="radio" name="nsr" value="<new>" '.($_REQUEST['nsr']== '<new>' ? 'CHECKED' : '').'></td>');
        ptln( '        <td align="left" nowrap><input type="text" name="newns" value="'.formtext($opts['newns']).'" class="edit" onChange="setradio(document.frm.nsr, \'<new>\');" /></td>');
        ptln( '      </tr>');
        ptln( '      <tr>');
        ptln( '        <td align="right" nowrap><label><span>'.$this->lang['pm_newname'].'</span></label></td>');
        ptln( '        <td>&nbsp;</td>');
        ptln( '        <td align="left" nowrap><input type="text" name="pagename" value="'.formtext(isset($opts['newname']) ? $opts['newname'] : $opts['name']).'" class="edit" /></td>');
        ptln( '      </tr>');
        ptln( '      </tr>');
        ptln( '        <td colspan="3" align="center"><input type="submit" value="'.formtext($this->lang['pm_submit']).'" class="button" /></td>');
        ptln( '      </tr>');
        ptln( '    </table>');
        ptln( '  </fieldset>');
        ptln( '</form>');
        ptln( '</div>');
    }

    /**
     * modify the links in a backlink.
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_updatebacklinks($id, $links, $opts, &$brackets)
    {
      global $ID;

      //Get namespace of document we are editing
      $bns = getNS($id);
      
      //Create a clean version of the new name
      $cleanname = cleanID($opts['newname']);

      //Open backlink
      lock($id);
      $text = io_readFile(wikiFN($id),True);

      //Form new document id for this backlink
      $matches = array();
      if ( $bns == $opts['newns'] ) { //Document is in same namespace as backlink
        $nid = '';
      } elseif ( preg_match('#^$bns:(.*)$#', $opts['newns'], $matches) ) { //Document is in sub-namespace of backlink
        $nid = '.:'.$matches[1].':';
      } else {  // Use absolute reference
        $nid = $opts['newns'].':';
      }

      //Form an array of possible old document id
      $matches = array();
      $oid = array();
      if ( $bns == $opts['ns'] ) { //Document was in same namespace as backlink
        foreach ( $links as $link ) {
          $oid[$link] = $nid.($cleanname == cleanID($link) ? $link : $opts['newname']);
          $oid['.:'.$link] = $nid.($cleanname == cleanID($link) ? $link : $opts['newname']);
        }
      }
      if ( preg_match('#^$bns:(.*)$#', $opts['ns'], $matches) ) { //Document was in sub-namespace of backlink
        foreach ( $links as $link ) {
          $oid['.:'.$matches[1].':'.$link] = $nid.($cleanname == cleanID($link) ? $link : $opts['newname']);
        }
      }
      // Use absolute reference
      foreach ( $links as $link ) {
        $oid[$opts['ns'].':'.$link] = $nid.($cleanname == cleanID($link) ? $link : $opts['newname']);
      }

      //Make the changes
      $this->_pm_updatelinks($text, $oid);

      //Save backlink and release lock
      saveWikiText($id, $text, sprintf($this->lang['pm_linkchange'], $ID, $opts['new_id']));
      unlock($id);
    }

    /**
     * modify the links using the pairs in $links
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_updatelinks(&$text, $links)
    {
      foreach( $links as $old => $new ) {
        $text = preg_replace( '#\[\[' . $old . '((\]\])|[\|\#])#i', '[[' . $new . '\1', $text);
      }
    }

    /**
     * modify the medialinks from namepspace $old to namespace $new
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_updatemedialinks(&$text, $old, $new)
    {
      //Question marks in media links need some extra handling
      $text = preg_replace('#\{\{' . $old . '([\?\|]|(\}\}))#i', '{{' . $new . '\1', $text);
    }

    /**
     * Get forward links in a given page
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_getforwardlinks($id)
    {
      $data = array();
      //get text
      $text = io_readfile(wikiFN($id));

      //match all links
      //FIXME may be incorrect because of code blocks
      //      CamelCase isn't supported, too
      preg_match_all('#\[\[(.+?)\]\]#si',$text,$matches,PREG_SET_ORDER);
      foreach($matches as $match){
        //get ID from link and discard most non wikilinks
        list($mid) = split('[\|#]',$match[1],2);
        if(preg_match('#^(https?|telnet|gopher|file|wais|ftp|ed2k|irc)://#',$mid)) continue;
        if(preg_match('#\w+>#',$mid)) continue;
        if(strpos($mid,'@') !== FALSE) continue;  //discard email addresses
        $mns = getNS($mid);
        $lnk = $mid;

       	//namespace starting with "." - prepend current namespace
        if(strpos($mns,'.')===0){
          $mid = getNS($id).':'.substr($mid,1);
        } elseif($mns===false){
          //no namespace in link? add current
          $mid = getNS($id).':'.$mid;
        }
        $data[$lnk] = (strpos($lnk,':')===0 ? ':' : '' ). cleanID($mid);
      }
      return $data;
    }

    /**
     * Get media links in a given page
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_getmedialinks($id)
    {
      $data = array();
      //get text
      $text = io_readfile(wikiFN($id));

      //match all links
      //FIXME may be incorrect because of code blocks
      //      CamelCase isn't supported, too
      preg_match_all('#{{(.+^>.+?)}}#si',$text,$matches,PREG_SET_ORDER);
      foreach($matches as $match){
        //get ID from link and discard most non wikilinks
        list($mid) = split('(\?|\|)',$match[1],2);
        $mns = getNS($mid);
        $lnk = $mid;

       	//namespace starting with "." - prepend current namespace
        if(strpos($mns,'.')===0){
          $mid = getNS($id).':'.substr($mid,1);
        } elseif($mns===false){
          //no namespace in link? add current
          $mid = getNS($id).':'.$mid;
        }
        $data[$lnk] = preg_replace('#:+#',':',$mid);
      }
      return $data;
    }
    
    /**
     * move meta files (Old Revs, Subscriptions, Meta, etc)
     *
     * This function meta files between directories
     *
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_movemeta($dir, $regex, $opts){
      global $conf;

      $old_path = $conf[$dir].'/'.str_replace(':','/',$opts['ns']).'/';
      $new_path = $conf[$dir].'/'.str_replace(':','/',$opts['newns']).'/';
      $dh = @opendir($old_path);
      if($dh) {
        while(($file = readdir($dh)) !== false){
          if(preg_match('/^\./',$file)) continue; //skip hidden files and upper dirs
          if(is_file($old_path.$file) and preg_match($regex,$file)){
            io_mkdir_p($new_path);
            io_rename($old_path.$file,$new_path.str_replace($opts['name'], $opts['newname'], $file));
            continue;
          }
        }
        closedir($dh);
      }
    }
    

    /**
     * recurse directory
     *
     * This function recurses into a given base directory
     * and calls the supplied function for each file and directory
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _pm_search(&$data,$base,$func,$opts,$dir='',$lvl=1){
      $dirs   = array();
      $files  = array();

      //read in directories and files
      $dh = @opendir($base.'/'.$dir);
      if(!$dh) return;
      while(($file = readdir($dh)) !== false){
        if(preg_match('/^\./',$file)) continue; //skip hidden files and upper dirs
        if(is_dir($base.'/'.$dir.'/'.$file)){
          $dirs[] = $dir.'/'.$file;
          continue;
        }
        $files[] = $dir.'/'.$file;
      }
      closedir($dh);
      sort($files);
      sort($dirs);

      //give directories to userfunction then recurse
      foreach($dirs as $dir){
        if ($this->$func($data,$base,$dir,'d',$lvl,$opts)){
          $this->_pm_search($data,$base,$func,$opts,$dir,$lvl+1);
        }
      }
      //now handle the files
      foreach($files as $file){
        $this->$func($data,$base,$file,'f',$lvl,$opts);
      }
    }

    /**
     * Search for backlinks to a given page
     *
     * $opts['ns']    namespace of the page
     * $opts['name']  name of the page without namespace
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function _pm_search_backlinks(&$data,$base,$file,$type,$lvl,$opts){
      //we do nothing with directories
      if($type == 'd') return true;;
      //only search txt files
      if(!preg_match('#\.txt$#',$file)) return true;;
    
      //get text
      $text = io_readfile($base.'/'.$file);
    
      //absolute search id
      $sid = cleanID($opts['ns'].':'.$opts['name']);
    
      //construct current namespace
      $cid = pathID($file);
      $cns = getNS($cid);

      //match all links
      //FIXME may be incorrect because of code blocks
      //      CamelCase isn't supported, too
      preg_match_all('#\[\[(.+?)\]\]#si',$text,$matches,PREG_SET_ORDER);
      foreach($matches as $match){
        //get ID from link and discard most non wikilinks
        list($mid) = split('[\|#]',$match[1],2);
        if(preg_match('#^(https?|telnet|gopher|file|wais|ftp|ed2k|irc)://#',$mid)) continue;
        if(preg_match('#\w+>#',$mid)) continue;
        if(strpos($mid,'@') !== FALSE) continue;  //discard email addresses

        $mns = getNS($mid);
       	//namespace starting with "." - prepend current namespace
        if(strpos($mns,'.')===0){
          $mid = $cns.(strpos($mns,':')===1 ?'' : ':').substr($mid,1);
        }
        if($mns===false){
          //no namespace in link? add current
          $mid = "$cns:$mid";
        }

        if (cleanID($mid) == $sid and (!isset($data[$cid]) or !in_array(noNS($mid), $data[$cid])) ){
          $data[$cid][] = noNS($mid);
        }
      }
    }

}