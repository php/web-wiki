<?php
/*
 * HCalendar plugin, configuration metadata
 *
 * @author    Juergen A. Lamers <jaloma.ac@googlemail.com>
 */
$meta['tag_vevent']    = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_summary']   = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_dtstart']   = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_dtend']     = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_location']  = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_uid']       = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));
$meta['tag_dtstamp']   = array('multichoice', '_choices' => array('div','abbr','span','p','h4','h5'));

$meta['itag_vevent']   = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_summary']  = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_dtstart']  = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_dtend']    = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_location'] = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_uid']      = array('multichoice', '_choices' => array('abbr','span'));
$meta['itag_dtstamp']  = array('multichoice', '_choices' => array('abbr','span'));

$meta['locale'] = array('string');

$meta['filterdate'] = array('onoff');
$meta['filterinlinedate'] = array('onoff');
