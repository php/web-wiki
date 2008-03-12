<?php
/**
 * master.php.net Login backend with dokuwiki Plaintext authentication fallback
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Lukas Smith <smith@pooteeweet.org>
 */

require_once(dirname(__FILE__).'/plain.class.php');

class auth_phpcvs extends auth_plain {
    /**
     * Constructor
     *
     * Carry out sanity checks to ensure the object is
     * able to operate. Set capabilities.
     *
     * @author  Christopher Smith <chris@jalakai.co.uk>
     */
    function auth_phpcvs() {
      parent::auth_plain();
    }

    /**
     * Set a user read via master.php.net
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @return  bool true or int error code
     */
    function _setCVSUser($user){
      $this->users[$user]['pass'] = null;
      $this->users[$user]['name'] = $user;
      $this->users[$user]['mail'] = $user.'@php.net';
      $this->users[$user]['grps'] = array('phpcvs');
    }

    /**
     * Check user+password against master.php.net [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @return  bool true or int error code
     */
    function _checkCVSPass($user,$pass = ''){
      $post = http_build_query(
        array(
          "token" => getenv("dokuwikitoken"),
          "username" => $user,
          "password" => $pass,
        ), '', '&'
      );

      $opts = array(
        "method"  => "POST",
        "header"  => "Content-type: application/x-www-form-urlencoded",
        "content" => $post,
      );

      $ctx = stream_context_create(array("http" => $opts));

      $s = file_get_contents("http://master.php.net/fetch/cvsauth.php", false, $ctx);

      $a = unserialize($s);
      /*
      define("E_UNKNOWN", 0);
      define("E_USERNAME", 1);
      define("E_PASSWORD", 2);
      */
      if (!is_array($a)) {
        return 0;
      }
      if (isset($a["errno"])) {
        return $a["errno"];
      }

      $this->_setCVSUser($user);

      return true;
    }

    /**
     * Check user+password [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @return  bool
     */
    function checkPass($user,$pass){
      $cvs_reply = $this->_checkCVSPass($user,$pass);

      if ($cvs_reply === true) {
        return true;
      } elseif($cvs_reply == 1) {
        return parent::checkPass($user,$pass);
      }

      return false;
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function getUserData($user){
      $cvs_reply = $this->_checkCVSPass($user);
      if ($cvs_reply === 2) {
        $this->_setCVSUser($user);
      }

      if($this->users === null) $this->_loadUserData();
      return isset($this->users[$user]) ? $this->users[$user] : false;
    }

    /**
     *  Remove one or more users from the list of registered users
     *
     *  @author  Christopher Smith <chris@jalakai.co.uk>
     *  @param   array  $users   array of users to be deleted
     *  @return  int             the number of users deleted
     */
    function deleteUsers($users) {
      $deleted = array();
      foreach ($users as $key => $user) {
        $cvs_reply = $this->_checkCVSPass($user);
        // user exists because we got a password mismatch error
        if($cvs_reply === 2) {
          unset($users[$key]);
        }
      }

      $users = array_values($users);

      return parent::deleteUsers($users);
    }
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
