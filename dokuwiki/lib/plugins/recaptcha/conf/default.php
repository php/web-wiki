<?php
/**
 * Options for the recaptcha plugin
 *
 * @author Adrian Schlegel <adrian.schlegel@liip.ch>
 */

$conf['publickey']  = '6LfbQQIAAAAAAHh-VSMCG6mUyYqrc5IjaWp8h4G-';
// ask lsmith@php.net for the key
$conf['privatekey'] = getenv('dokuwikirecaptcha');
$conf['theme'] = 'red';
$conf['lang'] = 'en';
