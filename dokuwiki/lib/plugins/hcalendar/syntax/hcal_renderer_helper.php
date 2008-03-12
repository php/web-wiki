<?php
/**
 * Plugin hcalendar: Using Microformat Calendar
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Juergen A. Lamers <jaloma.ac@googlemail.com>
 * @seealso    (http://jaloma.ac.googlepages.com/plugin:hcalendar)
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class helper_plugin_hcal extends DokuWiki_Plugin {

  function buildText(
		     $yy_start,$mth_start,$dy_start,$hh_start,$mm_start,$ss_start,$dt_start,
		     $yy_end  ,$mth_end  ,$dy_end  ,$hh_end,  $mm_end,  $ss_end,  $dt_end,
		     $summary ,$location, $inlineentry,
		     $tag_vevent,
		     $tag_summary,
		     $tag_dtstart,
		     $tag_dtend,
		     $tag_location,
		     $tag_uid,
		     $tag_dtstamp
		     ) {
    $txt = '';

    $txt .= '<'.$this->getConf($tag_vevent).' class="vevent">';
    $txt .= '<'.$this->getConf($tag_summary).' class="summary">'.$summary.' </'.$this->getConf($tag_summary).'>';
    if ($hh_start != '') {//1998-03-12T08:30:00-05:00
      $txt .= '<'.$this->getConf($tag_dtstart).' class="dtstart" title="'.$yy_start.'-'.$mth_start.'-'.$dy_start.'T'.$hh_start.':'.$mm_start.':'.$ss_start.//'+01:00'.//'Z'.
	'">'.
	date('D d.F Y',$dt_start).
	' ';
      if (isset($dt_end) && $hh_end != '') {
	$txt .= $this->getLang('ab');
      } else {
	$txt .= $this->getLang('um');
      }
      $txt .= ' '.$hh_start.':'.$mm_start.' '.$this->getLang('uhr').' ';
      $txt .= '</'.$this->getConf($tag_dtstart).'>';
      if (isset($dt_end)) {
	$txt .= ' <'.$this->getConf($tag_dtend).' class="dtend" title="'.$yy_end.'-'.$mth_end.'-'.$dy_end;
	if ($hh_end != '') {
	  $txt .= 'T'.$hh_end.':'.$mm_end.':'.$ss_end.//'-00:00'.//'Z'.
	    '';
	}
	$txt .= '">';
	$txt .= ' '.$this->getLang('bis').' ';
	if (($dy_end  != '' && $dy_end != $dy_start) ||
	    ($mth_end != '' && $mth_end != $mth_start) ||
	    ($yy_end  != '' && $yy_end != $yy_start)) {
	  $txt .= date('D d.F Y',$dt_end).' ';
	}
	$txt .= $this->getLang('um').' '.$hh_end.':'.$mm_end.' '.$this->getLang('uhr').' ';
	$txt .= '</'.$this->getConf($tag_dtend).'>';
      }
    } else {
      $txt .= ' <'.$this->getConf($tag_dtstart).' class="dtstart" title="'.$yy_start.'-'.$mth_start.'-'.$dy_start.'">'.
	//	    $dy_start.'.'.$mth_start.' '.$yy_start.
	date('D d.F Y',$dt_start).
	' </'.$this->getConf($tag_dtstart).'>';
      if (isset($dt_end) && (($dy_end != $dy_start) ||
			     ($mth_end != $mth_start) ||
			     ($yy_end != $yy_start))) {
	$txt .= ' '.$this->getLang('bis').' ';
	$txt .= date('D d.F Y',$dt_end).' ';
      }
    }
    $txt .= ' <'.$this->getConf($tag_location).' class="location">'.$location.'</'.$this->getConf($tag_location).'>';
    if (!$inlineentry) {
      $dID  = cleanID($summary);
      $txt .= ' <'.$this->getConf($tag_uid).' class="uid" style="font-size:4pt;">'.md5($dID).'</'.$this->getConf($tag_uid).'>';// Eingetragen von...
    }
    $txt .= '</'.$this->getConf($tag_vevent).'>';//class=vevent
    return $txt;
  }
}
