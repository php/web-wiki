<?php
/**
 * DokuWiki Plugin smtp (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_smtp extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('MAIL_MESSAGE_SEND', 'BEFORE', $this, 'handle_mail_message_send');

    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_mail_message_send(Doku_Event &$event, $param) {
        require_once __DIR__ . '/loader.php';

        // prepare the message
        /** @var Mailer $mailer Our Mailer with all the data */
        $mailer = $event->data['mail'];
        $body = $mailer->dump();  // this also prepares all internal variables of the mailer
        $rcpt   = $event->data['to'] . ',' .
                  $event->data['cc'] . ',' .
                  $event->data['bcc'];
        $from   = $event->data['from'];
        $message = new \splitbrain\dokuwiki\plugin\smtp\Message(
            $from,
            $rcpt,
            $body
        );

        // prepare the SMTP communication lib
        $logger = new \splitbrain\dokuwiki\plugin\smtp\Logger();
        $smtp = new \Tx\Mailer\SMTP($logger);
        $smtp->setServer(
            $this->getConf('smtp_host'),
            $this->getConf('smtp_port'),
            $this->getConf('smtp_ssl')
        );
        if($this->getConf('auth_user')){
            $smtp->setAuth(
                $this->getConf('auth_user'),
                $this->getConf('auth_pass')
            );
        }
        $smtp->setEhlo(
            helper_plugin_smtp::getEHLO($this->getConf('localdomain'))
        );


        // send the message
        try {
            $smtp->send($message);
            $ok = true;
        } catch (Exception $e) {
            msg('There was an unexpected problem communicating with SMTP: '.$e->getMessage(), -1);
            $ok = false;
        }

        // give debugging help on error
        if(!$ok && $this->getConf('debug')) {
            $log = array();
            foreach($logger->getLog() as $line) {
                $log[] = trim($line[1]);
            }
            $log = trim(join("\n", $log));
            msg('SMTP log:<br /><pre>'.hsc($log).'</pre><b>Above may contain passwords - do not post online!</b>',-1);
        }

        // finish event handling
        $event->preventDefault();
        $event->stopPropagation();
        $event->result = $ok;
        $event->data['success'] = $ok;
    }

}

// vim:ts=4:sw=4:et:
