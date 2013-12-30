<?php
/**
 * Doodle Plugin 2.0: helps to schedule meetings
 *
 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @url     http://www.dokuwiki.org/plugin:doodle2
 * @author  Robert Rackl <wiki@doogie.de>
 * @author	Jonathan Tsai <tryweb@ichiayi.com>
 * @author  Esther Brunner <wikidesign@gmail.com>
 * @author  Romain Coltel <aorimn@gmail.com>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * Displays a table where users can vote for some predefined choices
 * Syntax:
 * 
 * <pre>
 * <doodle
 *   title="What do you like best?"
 *   auth="none|ip|user"
 *   adminUsers="user1|user2"
 *   adminGroups="group1|group2"
 *   voteType="default|multi"
 *   closed="true|false" >
 *     * Option 1 
 *     * Option 2 **some wikimarkup** \\ is __allowed__!
 *     * Option 3
 * </doodle>
 * </pre>
 *
 * Only required parameteres are a title and at least one option.
 *
 * <h3>Parameters</h3>
 * auth="none" - everyone can vote with any username, (IPs will be recorded but not checked)
 * auth="ip"   - everyone can vote with any username, votes will be tracked by IP to prevent duplicate voting
 * auth="user" - users must login with a valid dokuwiki user. This has the advantage, that users can
 *               edit their vote ("change their mind") later on.
 *
 * <h3>adminUsers and adminGroups</h3>
 * "|"-separated list of adminUsers or adminGroups, whose members can always edit and delete <b>any</b> entry.
 *
 * <h3>Vote Type</h3>
 * default    - user can vote for exactly one option (round checkboxes will be shown)
 * multi      - can choose any number of options, including none (square checkboxes will be shown).
 *
 * If closed=="true", then no one can vote anymore. The result will still be shown on the page.
 *
 * The doodle's data is saved in '<dokuwiki>/data/meta/title_of_vote.doodle'. The filename is the (masked) title. 
 * This has the advantage that you can move your doodle to another page, without loosing the data.
 */
class syntax_plugin_doodle extends DokuWiki_Syntax_Plugin 
{
    const AUTH_NONE = 0;
    const AUTH_IP   = 1;
    const AUTH_USER = 2;

    /**
     * return info about this plugin
     */
    function getInfo() {
        return array(
            'author' => 'Robert Rackl',
            'email'  => 'wiki@doogie.de',
            'date'   => '2010/10/26',
            'name'   => 'Doodle Plugin 2.0',
            'desc'   => 'helps to schedule meetings',
            'url'    => 'http://wiki.splitbrain.org/plugin:doodle2',
        );
    }

    function getType()  { return 'substition';}
    function getPType() { return 'block';}
    function getSort()  { return 168; }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<doodle\b.*?>.+?</doodle>', $mode, 'plugin_doodle');
    }

    /**
     * Handle the match, parse parameters & choices
     * and prepare everything for the render() method.
     */
    function handle($match, $state, $pos, &$handler) {
        $match = substr($match, 8, -9);              // strip markup (including space after "<doodle ")
        list($parameterStr, $choiceStr) = preg_split('/>/u', $match, 2);

        //----- default parameter settings
        $params = array(
            'title'          => 'Default title',
            'auth'           => self::AUTH_NONE,
            'adminUsers'     => '',
            'adminGroups'    => '',
            'adminMail'      => null,
            'voteType'       => 'default',
            'closed'         => FALSE
        );

        //----- parse parameteres into name="value" pairs  
        preg_match_all("/(\w+?)=\"(.*?)\"/", $parameterStr, $regexMatches, PREG_SET_ORDER);
        //debout($parameterStr);
        //debout($regexMatches);
        for ($i = 0; $i < count($regexMatches); $i++) {
            $name  = strtoupper($regexMatches[$i][1]);  // first subpattern: name of attribute in UPPERCASE
            $value = $regexMatches[$i][2];              // second subpattern is value
            if (strcmp($name, "TITLE") == 0) {
                $params['title'] = hsc(trim($value));
            } else
            if (strcmp($name, "AUTH") == 0) {
               if (strcasecmp($value, 'IP') == 0) {  
                   $params['auth'] = self::AUTH_IP;
               } else 
               if (strcasecmp($value, 'USER') == 0) {
                   $params['auth'] = self::AUTH_USER;
               }
            } else
            if (strcmp($name, "ADMINUSERS") == 0) {
                $params['adminUsers'] = $value;
            } else
            if (strcmp($name, "ADMINGROUPS") == 0) {
                $params['adminGroups'] = $value;
            } else
            if (strcmp($name, "ADMINMAIL") == 0) {
                // check for valid email adress
                if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$/', $value)) {
                    $params['adminMail'] = $value;
                }
            } else 
            if (strcmp($name, "VOTETYPE") == 0) {
                if (preg_match('/default|multi/', $value)) {
                    $params['voteType'] = $value;
                }
            } else 
            if ((strcmp($name, "CLOSEON") == 0) &&
                (($timestamp = strtotime($value)) !== false) &&
                (time() > $timestamp)   ) 
            {
                $params['closed'] = 1;
            } else
            if (strcmp($name, "CLOSED") == 0) {
                $params['closed'] = strcasecmp($value, "TRUE") == 0;
            } else
            if (strcmp($name, "SORT") == 0) {
                $params['sort'] = $value;  // make it possible to sort by time
            }
        }

        // (If there are no choices inside the <doodle> tag, then doodle's data will be reset.)
        $choices = $this->parseChoices($choiceStr);
        
        $result = array('params' => $params, 'choices' => $choices);
        //debout('handle returns', $result);
        return $result;
    }

    /**
     * parse list of choices
     * explode, trim and encode html entities,
     * empty choices will be skipped.
     */
    function parseChoices($choiceStr) {
        $choices = array();
        preg_match_all('/^   \* (.*?)$/m', $choiceStr, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[1] as $choice) {
            $choice = hsc(trim($choice));
            if (!empty($choice)) {
                $choice = preg_replace('#\\\\\\\\#', '<br />', $choice);       # two(!) backslashes for a newline
                $choice = preg_replace('#\*\*(.*?)\*\*#', '<b>\1</b>', $choice);   # bold
                $choice = preg_replace('#__(.*?)__#', '<u>\1</u>', $choice);   # underscore
                $choice = preg_replace('#//(.*?)//#', '<i>\1</i>', $choice);   # italic
                $choices []= $choice;
            }
        }
        //debout($choices);
        return $choices;
    }

    // ----- these fields will always be initialized at the beginning of the render function
    //       and can then be used in helper functions below.
    public $params    = array();
    public $choices   = array();
    public $doodle    = array();
    public $template  = array();   // output values for doodle_template.php

    /**
     * Read doodle data from file,
     * add new vote if user just submitted one and
     * create output xHTML from template
     */
    function render($mode, &$renderer, $data) {
        if ($mode != 'xhtml') return false;
        
        //debout("render: $mode");        
        global $lang;
        global $auth;
        global $conf; 
        global $INFO; // needed for users real name
        global $ACT;  // action from $_REQUEST['do']
        global $REV;  // to not allow any action if it's an old page        
        global $ID;   // name of current page

        //debout('data in render', $data);

        $this->params    = $data['params'];
        $this->choices   = $data['choices'];
        $this->doodle    = array();
        $this->template  = array();
        
        // prevent caching to ensure the poll results are fresh
        $renderer->info['cache'] = false;

        // ----- read doodle data from file (if there are choices given and there is a file)
        if (count($this->choices) > 0) {
            $this->doodle = $this->readDoodleDataFromFile();
        }

        //FIXME: count($choices) may be different from number of choices in $doodle data!

        $can_vote = false;
        if ($INFO["userinfo"] && count(array_intersect(array('admin', 'phpcvs', 'voting'), $INFO['userinfo']['grps'])) > 0) {
            $can_vote = true;
        }
        // ----- FORM ACTIONS (only allowed when showing the most recent version of the page, not when editing) -----
        $formId =  'doodle__form__'.cleanID($this->params['title']);
        if ($ACT == 'show' && $_REQUEST['formId'] == $formId && $REV == false && $can_vote) {
            // ---- cast new vote
            if (!empty($_REQUEST['cast__vote'])) {
                $this->castVote();
            } else        
            // ---- start editing an entry
            if (!empty($_REQUEST['edit__entry']) ) {
                $this->startEditEntry();
            } else
            // ---- save changed entry
            if (!empty($_REQUEST['change__vote']) ) {
                $this->castVote();
            } else
            // ---- delete an entry completely
            if (!empty($_REQUEST['delete__entry']) ) {
                $this->deleteEntry();
            }
        }
        
        /******** Format of the $doodle array ***********
         * The $doodle array maps fullnames (with html special characters masked) to an array of userData for this vote.
         * Each sub array contains:
         *   'username' loggin name if use was logged in
         *   'choices'  is an (variable length!) array of column indexes where user has voted
         *   'ip'       ip of voting machine
         *   'time'     unix timestamp when vote was casted
         
        
        $doodle = array(
          'Robert' => array(
            'username'  => 'doogie'
            'choices'   => array(0, 3),
            'ip'        => '123.123.123.123',
            'time'      => 1284970602
          ),
          'Peter' => array(
            'choices'   => array(),
            'ip'        => '222.122.111.1',
            'time'      > 12849702333
          ),
          'Sabine' => array(
            'choices'   => array(0, 1, 2, 3, 4),
            'ip'        => '333.333.333.333',
            'time'      => 1284970222
          ),
        );
        */
        
        // ---- fill $this->template variable for doodle_template.php (column by column)
        $this->template['title']      = hsc($this->params['title']);
        $this->template['choices']    = $this->choices;
        $this->template['result']     = $this->params['closed'] ? $this->getLang('final_result') : $this->getLang('count');
        $this->template['doodleData'] = array();  // this will be filled with some HTML snippets
        $this->template['formId']     = $formId;
        if ($this->params['closed']) {
            $this->template['msg'] = $this->getLang('poll_closed');
        }
        
        for($col = 0; $col < count($this->choices); $col++) {
            $this->template['count'][$col] = 0;
            foreach ($this->doodle as $fullname => $userData) {
                if (!empty($userData['username'])) {
                  $this->template['doodleData']["$fullname"]['username'] = '&nbsp;('.$userData['username'].')';
                }
                if (in_array($col, $userData['choices'])) {
                    $timeLoc = strftime($conf['dformat'], $userData['time']);  // localized time of vote
                    $this->template['doodleData']["$fullname"]['choice'][$col] = 
                        '<td  class="centeralign" style="background-color:#AFA"><img src="'.DOKU_BASE.'lib/images/success.png" title="'.$timeLoc.'"></td>';
                    $this->template['count']["$col"]++;
                } else {
                    $this->template['doodleData']["$fullname"]['choice'][$col] = 
                        '<td  class="centeralign" style="background-color:#FCC">&nbsp;</td>';
                }                
            }
        }
        
        // ---- add edit link to editable entries
        foreach($this->doodle as $fullname => $userData) {
            if ($ACT == 'show' && $REV == false &&
                $this->isAllowedToEditEntry($fullname)) 
            {
                // the javascript source of these functions is in script.js
                $this->template['doodleData']["$fullname"]['editLinks'] = 
                   '<a href="javascript:editEntry(\''.$formId.'\',\''.$fullname.'\')">'.
                   '  <img src="'.DOKU_BASE.'lib/images/pencil.png" alt="edit entry" style="float:left">'.
                   '</a>'.
                   '<a href="javascript:deleteEntry(\''.$formId.'\',\''.$fullname.'\')">'.
                   '  <img src="'.DOKU_BASE.'lib/images/del.png" alt="delete entry" style="float:left">'.
                   '</a>';
            }
        }

        // ---- calculates if user is allowed to vote
        $this->template['inputTR'] = ($can_vote?$this->getInputTR():'');
        
        // ----- I am using PHP as a templating engine here.
        //debout("Template", $this->template);
        ob_start();
        include 'doodle_template.php';  // the array $template can be used inside doodle_template.php!
        $doodle_table = ob_get_contents();
        ob_end_clean();
        $renderer->doc .= $doodle_table;
    }

    // --------------- FORM ACTIONS -----------
    /** 
     * ACTION: cast a new vote 
     * or save a changed vote
     * (If user is allowed to.)
     */
    function castVote() {
        $fullname          = hsc(trim($_REQUEST['fullname'])); 
        $selected_indexes  = $_REQUEST['selected_indexes'];  // may not be set when all checkboxes are deseleted.

        if (empty($fullname)) {
            $this->template['msg'] = $this->getLang('dont_have_name');
            return;
        }
        if (empty($selected_indexes)) {
            if ($this->params['voteType'] == 'multi') {
                $selected_indexes = array();   //allow empty vote only if voteType is "multi"
            } else {
                $this->template['msg'] = $this->getLang('select_one_option');
                return;
            }
        }
        
        //---- check if user is allowed to vote, according to 'auth' parameter
        
        //if AUTH_USER, then user must be logged in
        if ($this->params['auth'] == self::AUTH_USER  && !$this->isLoggedIn()) {
            $this->template['msg'] = $this->getLang('must_be_logged_in');
            return;
        }
        
        //if AUTH_IP, then prevent duplicate votes by IP.
        //Exception: If user is logged in he is always allowed to change the vote with his fullname, even if he is on another IP.
        if ($this->params['auth'] == self::AUTH_IP && !$this->isLoggedIn() && !isset($_REQUEST['change__vote']) ) {
            foreach($this->doodle as $existintFullname => $userData) {
              if (strcmp($userData['ip'], $_SERVER['REMOTE_ADDR']) == 0) {
                $this->template['msg'] = sprintf($this->getLang('ip_has_already_voted'), $_SERVER['REMOTE_ADDR']);
                return;
              }
            }
        }

        //do not vote twice, unless change__vote is set
        if (isset($this->doodle["$fullname"]) && !isset($_REQUEST['change__vote']) ) {
            $this->template['msg'] = $this->getLang('you_voted_already');
            return;
        }
        
        //check if change__vote is allowed
        if (!empty($_REQUEST['change__vote']) &&
            !$this->isAllowedToEditEntry($fullname)) 
        {
            $this->template['msg'] = $this->getLang('not_allowed_to_change');
            return;
        }

        if (!empty($_SERVER['REMOTE_USER'])) {
          $this->doodle["$fullname"]['username'] = $_SERVER['REMOTE_USER'];
        }
        $this->doodle["$fullname"]['choices'] = $selected_indexes;
        $this->doodle["$fullname"]['time']    = time();
        $this->doodle["$fullname"]['ip']      = $_SERVER['REMOTE_ADDR'];
        $this->writeDoodleDataToFile();
        $this->template['msg'] = $this->getLang('vote_saved');
        
        //send mail if  $params['adminMail'] is filled
        if (!empty($this->params['adminMail'])) {
            $subj = "[DoodlePlugin] Vote casted by $fullname (".$this->doodle["$fullname"]['username'].')';
            $body = 'User has casted a vote'."\n\n".print_r($this->doodle["$fullname"], true);
            mail_send($this->params['adminMail'], $subj, $body, $conf['mailfrom']);
        }
    }
    
    /** 
     * ACTION: start editing an entry 
     * expects fullname of voter in request param edit__entry
     */
    function startEditEntry() {
        $fullname = hsc(trim($_REQUEST['edit__entry']));
        if (!$this->isAllowedToEditEntry($fullname)) return;
            
        $this->template['editEntry']['fullname']         = $fullname;
        $this->template['editEntry']['selected_indexes'] = $this->doodle["$fullname"]['choices'];
        // $fullname will be shown in the input row
    }

    /** ACTION: delete an entry completely */
    function deleteEntry() {
        $fullname = hsc(trim($_REQUEST['delete__entry']));
        if (!$this->isAllowedToEditEntry($fullname))   return;

        unset($this->doodle["$fullname"]);
        $this->writeDoodleDataToFile();
        $this->template['msg'] = $this->getLang('vote_deleted');
    }
    
    // ---------- HELPER METHODS -----------

    /**
     * check if the currently logged in user is allowed to edit a given entry.
     * @return true if entryFullname is the entry of the current user, or
     *         the currently logged in user is in the list of admins
     */
    function isAllowedToEditEntry($entryFullname) {
        global $INFO;
        global $auth;

        if (empty($entryFullname)) return false;
        if (!isset($this->doodle["$entryFullname"])) return false;
        if ($this->params['closed']) return false;
        if (!$this->isLoggedIn()) return false;

        //check adminGroups
        if (!empty($this->params['adminGroups'])) {
            $adminGroups = explode('|', $this->params['adminGroups']); // array of adminGroups
            $usersGroups = $INFO['userinfo']['grps'];  // array of groups that the user is in
            if (count(array_intersect($adminGroups, $usersGroups)) > 0) return true;
        }
        
        //check adminUsers
        if (!empty($this->params['adminUsers'])) {
            $adminUsers = explode('|', $this->params['adminUsers']);
            return in_array($_SERVER['REMOTE_USER'], $adminUsers);
        }
        
        //check own entry
        return strcasecmp(hsc($INFO['userinfo']['name']), $entryFullname) == 0;  // compare real name
    }
    
    /** 
     * return true if the user is currently logged in
     */
    function isLoggedIn() {
        // see http://www.dokuwiki.org/devel:environment
        global $INFO;
        return isset($INFO['userinfo']); 
    }
    
    /**
     * calculate the input table row:
     * @return   complete <TR> tags for input row and information message
     * May return empty string, if user is not allowed to vote
     *
     * If user is logged in he is always allowed edit his own entry. ("change his mind")
     * If user is logged in and has already voted, empty string will be returned. 
     * If user is not logged in but login is required (auth="user"), then also return '';
     */
    function getInputTR() {
        global $ACT;
        global $INFO;        
        if ($ACT != 'show') return '';
        if ($this->params['closed']) return '';
        
        $fullname = '';
        $editMode = false;
        if ($this->isLoggedIn()) {
            $fullname = $INFO['userinfo']['name']; 
            if (isset($this->template['editEntry'])) {
                $fullname = $this->template['editEntry']['fullname'];
                $editMode = true;
            } else {
                if (isset($this->doodle["$fullname"]) ) return '';
            }
        } else {
            if ($this->params['auth'] == self::AUTH_USER) return '';
        }

        // build html for tr
        $c = count($this->choices);
        $TR  = '';
        //$TR .= '<tr style="height:3px"><th colspan="'.($c+1).'"></th></tr>';
        $TR .= '<tr>';
        $TR .= '<td class="rightalign">';
        if ($fullname) {
            if ($editMode) $TR .= $this->getLang('edit').':&nbsp;';
            $TR .= $fullname.'&nbsp;('.$_SERVER['REMOTE_USER'].')';
            $TR .= '<input type="hidden" name="fullname" value="'.$fullname.'">';
        } else {
            $TR .= '<input type="text" name="fullname" value="">';
        }
        $TR .='</td>';
       
        for($col = 0; $col < $c; $col++) {
            $selected = '';
            if ($editMode && in_array($col, $this->template['editEntry']['selected_indexes']) ) {
                $selected = 'checked="checked"';
            }
            $TR .= '<td class="centeralign">';
            
            if ($this->params['voteType'] == 'multi') {
                $inputType = "checkbox";
            } else {
                $inputType = "radio";
            }
            $TR .= '<input type="'.$inputType.'" name="selected_indexes[]" value="'.$col.'"';
            $TR .= $selected.">";
            $TR .= '</TD>';
        }

        $TR .= '</tr>';
        $TR .= '<tr>';
        $TR .= '  <td colspan="'.($c+1).'" class="centeralign">';
        
        if ($editMode) {
            $TR .= '    <input type="submit" id="voteButton" value=" '.$this->getLang('btn_change').' " name="change__vote" class="button">';
        } else {
            $TR .= '    <input type="submit" id="voteButton" value=" '.$this->getLang('btn_vote').' " name="cast__vote" class="button">';
        }
        $TR .= '  </td>';
        $TR .= '</tr>';
        
        return $TR;
    }
    
    
    /**
     * Loads the serialized doodle data from the file in the metadata directory.
     * If the file does not exist yet, an empty array is returned.
     * @return the $doodle array
     * @see writeDoodleDataToFile()
     */
    function readDoodleDataFromFile() {
        $dfile     = $this->getDoodleFileName();
        $doodle    = array();
        if (file_exists($dfile)) {
            $doodle = unserialize(file_get_contents($dfile));
        }
        //sanitize: $doodle[$fullnmae]['choices'] must be at least an array
        //          This may happen if user deselected all choices
        foreach($doodle as $fullname => $userData) {
            if (!is_array($doodle["$fullname"]['choices'])) {
                $doodle["$fullname"]['choices'] = array();
            }
        }
        
        if (strcmp($this->params['sort'], 'time') == 0) {
            debout("sorting by time");
            uasort($doodle, 'cmpEntryByTime');
        } else {
            uksort($doodle, "strnatcasecmp"); // case insensitive "natural" sort
        }
        //debout("read from $dfile", $doodle);
        return $doodle;
    }
    
    /**
     * serialize the doodles data to a file
     */
    function writeDoodleDataToFile() {
        if (!is_array($this->doodle)) return;
        $dfile = $this->getDoodleFileName();
        uksort($this->doodle, "strnatcasecmp"); // case insensitive "natural" sort
        io_saveFile($dfile, serialize($this->doodle));
        //debout("written to $dfile", $doodle);
        return $dfile;
    }
    
    /**
     * create unique filename for this doodle from its title.
     * (replaces space with underscore etc.)
     */
    function getDoodleFileName() {
        if (empty($this->params['title'])) {
          debout('Doodle must have title.');
          return 'doodle.doodle';
        }
        $dID       = hsc(trim($this->params['title']));
        $dfile     = metaFN($dID, '.doodle');       // serialized doodle data file in meta directory
        return $dfile;        
    }


} // end of class

// ----- static functions

/** compare two doodle entries by the time of vote */
function cmpEntryByTime($a, $b) {
    return strcmp($a['time'], $b['time']);
}


function debout() {
    if (func_num_args() == 1) {
        msg('<pre>'.hsc(print_r(func_get_arg(0), true)).'</pre>');
    } else if (func_num_args() == 2) {
        msg('<h2>'.func_get_arg(0).'</h2><pre>'.hsc(print_r(func_get_arg(1), true)).'</pre>');
    }
    
}

?>
