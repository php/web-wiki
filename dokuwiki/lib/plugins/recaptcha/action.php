<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

require_once(dirname(__FILE__).'/lib/recaptchalib.php');
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl2.html)
 * @author     Adrian Schlegel <adrian.schlegel@liip.ch>
 */

class action_plugin_recaptcha extends DokuWiki_Action_Plugin {

    private $recaptchaLangs = array('en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr');

    /**
     * get plugin info
     *
     */
    function getInfo()
    {
        return confToHash(dirname(__FILE__).'../info.txt');
    }

    /**
     * register an event hook
     *
     */
    function register(&$controller)
    {
        // only register the hooks if the necessary config paramters exist
        if($this->getConf('publickey') && $this->getConf('privatekey')) {
            $controller->register_hook('ACTION_ACT_PREPROCESS',
                'BEFORE',
                $this,
                'preprocess',
                array());
            // new hook
            $controller->register_hook('HTML_REGISTERFORM_OUTPUT', 
                'BEFORE', 
                $this, 
                'insert',
                array('oldhook' => false));
            // old hook
            $controller->register_hook('HTML_REGISTERFORM_INJECTION',
                                       'BEFORE',
                                       $this,
                                       'insert',
                                       array('oldhook' => true));
        }
    }

    /**
     * insert html code for recaptcha into the register form
     *
     * @param obj $event
     * @param array $param
     */
    function insert(&$event, $param) {
        global $conf;

        $recaptcha = '<div style="width: 320px;"></div>';

        // see first if a language is defined for the plugin, if not try to use the language defined for dokuwiki
        $lang = $this->getConf('lang') ? $this->getConf('lang') : (in_array($conf['lang'], $this->recaptchaLangs) ? $conf['lang'] : 'en');
        $recaptcha .= "<script type='text/javascript'>
            var RecaptchaOptions = {";
        $recaptcha .= $this->getConf('theme') ? "theme: '".$this->getConf('theme')."'," : '';
        $recaptcha .= "lang: '".$lang."'";
        $recaptcha .= "
    };
    </script>";
        $recaptcha .= recaptcha_get_html($this->getConf('publickey'));

        if($param['oldhook']) {
            echo $recaptcha;
        } else {
            $pos = $event->data->findElementByAttribute('type','submit');
            $event->data->insertElement($pos++, $recaptcha);
        }
    }


    /**
     * process the answer to the captcha
     *
     * @param obj $event
     * @param array $param
     *
     */
    function preprocess(&$event, $param) {

        // get and clean the action
        $act = $this->_act_clean($event->data);
        // check if we are in a registration process
        if(!('register' == $act && $_POST['save'])) {
            return;
        }
        $resp = recaptcha_check_answer ($this->getConf('privatekey'),
            $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"],
            $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
            msg($this->getLang('testfailed'),-1);
            $_POST['save']  = false;
        }

    }

    /**
     * Pre-Sanitize the action command
     *
     * Similar to act_clean in action.php but simplified and without
     * error messages
     * (taken from Andreas Gohrs captcha plugin)
     */
    function _act_clean($act){
         // check if the action was given as array key
         if(is_array($act)){
           list($act) = array_keys($act);
         }

         //remove all bad chars
         $act = strtolower($act);
         $act = preg_replace('/[^a-z_]+/','',$act);

         return $act;
     }
} //end of action class
