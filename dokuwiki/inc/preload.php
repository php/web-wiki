<?php
/**
 * This is an example for a farm setup. Simply copy this file to preload.php and
 * uncomment what you need. See http://www.dokuwiki.org/farms for more information.
 * You can also use preload.php for other things than farming, e.g. for moving
 * local configuration files out of the main ./conf directory.
 */

// set this to your farm directory
//if(!defined('DOKU_FARMDIR')) define('DOKU_FARMDIR', '/var/www/farm');

// include this after DOKU_FARMDIR if you want to use farms
//include(fullpath(dirname(__FILE__)).'/farm.php');

if (getenv("WIKI_AUTH_FILE") !== false) {
	$config_cascade['plainauth.users'] = array(
		'default' => getenv('WIKI_AUTH_FILE'),
	);
}
