<?php

namespace splitbrain\dokuwiki\plugin\smtp;

/**
 * Class Message
 *
 * Overrides the Message class with what we need to reuse the SMTP mailer without using
 * their message composer
 *
 * @package splitbrain\dokuwiki\plugin\smtp
 */
class Message extends \Tx\Mailer\Message {

    protected $from;
    protected $rcpt;
    protected $body;

    /**
     * @param string $from Sender Address
     * @param string $rcpt all recipients (TO, CC, BCC)
     * @param string $body the full message body including headers
     */
    public function __construct($from, $rcpt, $body) {
        $this->from = $from;
        $this->rcpt = $rcpt;
        $this->body = $body;
    }

    /**
     * Return the mail only part of the from address
     *
     * @return string
     */
    public function getFromEmail() {
        if(preg_match('#(.*?)<(.*?)>#', $this->from, $matches)) {
            return $matches[2];
        }

        return $this->from;
    }

    /**
     * Get a list of all recipients (mail only part)
     *
     * @return array
     */
    public function getTo() {
        $rcpt = array();

        // We need the mail only part of all recipients
        $addresses = explode(',', $this->rcpt);
        foreach($addresses as $addr) {
            // parse address
            if(preg_match('#(.*?)<(.*?)>#', $addr, $matches)) {
                $rcpt[] = trim($matches[2]);
            } else {
                $rcpt[] = trim($addr);
            }
        }

        $rcpt = array_filter($rcpt);
        $rcpt = array_unique($rcpt);
        return $rcpt;
    }

    /**
     * Return the whole message body ready to be send by DATA
     *
     * Includes end of data signature and strips the BCC header
     *
     * @return string
     */
    public function toString() {
        // we need to remove the BCC header here
        $lines = preg_split('/\r?\n/', $this->body);
        $count = count($lines);
        for($i=0; $i<$count; $i++) {
            if(trim($lines[$i]) === '') break; // end of headers, we're done
            if(substr($lines[$i],0, 4) == 'Bcc:') {
                unset($lines[$i]); // we found the Bcc: header and remove it
                while(substr($lines[++$i],0, 1) === ' ') {
                    unset($lines[$i]); // indented lines are header continuiation
                }
                break; // header removed, we're done
            }
        }
        $body = join($this->CRLF, $lines);

        return $body . $this->CRLF . $this->CRLF . "." . $this->CRLF;
    }

}
