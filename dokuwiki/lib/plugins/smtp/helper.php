<?php
/**
 * DokuWiki Plugin smtp (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_smtp extends DokuWiki_Plugin {

    /**
     * Return a string usable as EHLO message
     *
     * @param string $ehlo configured EHLO (ovverrides automatic detection)
     * @return string
     */
    static public function getEHLO($ehlo='') {
        if(empty($ehlo)) {
            $ip = $_SERVER["SERVER_ADDR"];
            if (empty($ip))
              return "localhost.localdomain";

            // Indicate IPv6 address according to RFC 2821, if applicable.
            $colonPos = strpos($ip, ':');
            if ($colonPos !== false) {
                $ip = 'IPv6:'.$ip;
            }

            return "[" . $ip . "]";
        }
        return $ehlo;
    }

}

// vim:ts=4:sw=4:et:
