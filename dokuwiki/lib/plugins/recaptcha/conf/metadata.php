<?php
/**
 * Options for the recaptcha plugin
 *
 * @author Adrian Schlegel <adrian.schlegel@liip.ch>
 */

$meta['publickey']  = array('string');
$meta['privatekey'] = array('string');
$meta['theme'] = array('multichoice', '_choices'=>array('red', 'white', 'blackglass', 'custom'));
$meta['lang'] = array('multichoice', '_choices'=>array('en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr'));
