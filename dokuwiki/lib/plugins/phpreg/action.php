<?php

/**
 * Registration spam prevention.
 *
 * @license GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author  Christoph M. Becker <cmb@php.net>
 */

// must be run within Dokuwiki
use dokuwiki\Form\InputElement;

if(!defined('DOKU_INC')) die();

/**
 * The action class which is automatically instantiated by DokuWiki.
 *
 * @link https://www.dokuwiki.org/devel:action_plugins
 */
class action_plugin_phpreg extends DokuWiki_Action_Plugin
{
    /**
     * Registers event handlers.
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object.
     *
     * @return not required
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook(
            'FORM_REGISTER_OUTPUT',
            'BEFORE',
            $this,
            'handle_registerform_output'
        );
        $controller->register_hook(
            'ACTION_ACT_PREPROCESS',
            'BEFORE',
            $this,
            'handle_act_preprocess',
            array()
        );
    }

    /**
     * custom event handler
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  the parameters passed to register_hook when this
     *                           handler was registered
     *
     * @return   not required
     */
    public function handle_registerform_output(Doku_Event &$event, $param)
    {
        $pos = $event->data->findPositionByType('button');

        if (!$pos) return;
        $spam = $_POST['spam'] ?? '';
        $element = new InputElement(
            'text',
            'spam',
            'To which email address do you have to send an email to now?'
        );
        $element->val($spam);
        $event->data->addElement($element, $pos);
    }

    /**
     * Checks the spam-prevention.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  the parameters passed to register_hook when this
     *                           handler was registered
     *
     * @return   not required
     */
    public function handle_act_preprocess(Doku_Event &$event, $param)
    {
        $act = $event->data;
        if ($act != 'register' || !isset($_POST['save'])) {
            return;
        }
        $spam = isset($_POST['spam']) ? $_POST['spam'] : '';
        $spam = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/', '', $spam));
        if ($spam != 'internals@lists.php.net') {
            msg("That wasn't the answer we were expecting", -1);
            $_POST['save'] = false;
        }
    }
}
