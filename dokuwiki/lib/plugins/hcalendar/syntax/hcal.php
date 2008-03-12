<?php
/**
 * Plugin hcalendar: Using Microformat Calendar
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Juergen A. Lamers <jaloma.ac@googlemail.com>
 * @seealso    (http://jaloma.ac.googlepages.com/plugin:hcalendar)
 * @seealso    (http://microformats.org/wiki/internet-explorer-extensions)
 * @seealso    (http://microformats.org/wiki/firefox-extensions)
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_PLUGIN.'hcalendar/syntax/helper.php'); 
require_once(DOKU_PLUGIN.'hcalendar/syntax/hcal_renderer_helper.php'); 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_hcalendar_hcal extends DokuWiki_Syntax_Plugin {
 
  function getInfo(){
    return array(
		 'author' => 'Juergen A. Lamers',
		 'email'  => 'jaloma.ac@googlemail.com',
		 'date'   => '2008-01-20',
		 'name'   => 'HCalendar HCal Plugin',
		 'desc'   => 'Adds a HCalendar Items 
                     syntax: {{hcal>yyyy/mm/dd[hh:mm:ss];yyyy/mm/dd[hh:mm:ss]|summary|location}}
See: http://microformats.org/wiki/',
		 'url'    => 'http://jaloma.ac.googlepages.com/plugin:hcalendar',
		 );
  }
 
  function getType() { return 'substition'; }
  function getSort() { return 304; }

  function connectTo($mode) { 
    $this->Lexer->addSpecialPattern('{{hcal>[^}]*?}}',$mode,'plugin_hcalendar_hcal'); 
  }

  function handle($match, $state, $pos, &$handler){
    @list($summary, $location,
	  $yy_start, $mth_start, $dy_start, 
	  $hh_start, $mm_start,  $ss_start,
	  $tm_start,
	  $yy_end,   $mth_end,   $dy_end,
	  $hh_end,   $mm_end,    $ss_end,
	  $tm_end,
	  $err) = hcal_parseCommand($match);
    if (isset($err)) {
      return array('error',
		   $err);
    } else {
      return array('wiki',
		   $yy_start,$mth_start,$dy_start,$hh_start,$mm_start,$ss_start, $tm_start,
		   $yy_end,  $mth_end,  $dy_end,  $hh_end,  $mm_end,  $ss_end,   $tm_end,
		   $summary, $location);
    }
  }

  function render($mode, &$renderer, $data) {
    setlocale(LC_ALL,$this->getConf('locale'));
    list($style,
	 $yy_start,$mth_start,$dy_start,$hh_start,$mm_start,$ss_start,$dt_start,
	 $yy_end  ,$mth_end  ,$dy_end  ,$hh_end,  $mm_end,  $ss_end,  $dt_end,
	 $summary ,$location) = $data;

    if ($this->getConf('filterdate') == true) {
      $dt_now = strtotime("now");
      if ($dt_start < $dt_now && $dt_end < $dt_now) { return true;}
    }
    if($mode == 'xhtml'){
      switch($style) {
      case 'wiki':
	$renderer->doc .= helper_plugin_hcal::buildText(
		     $yy_start,$mth_start,$dy_start,$hh_start,$mm_start,$ss_start,$dt_start,
		     $yy_end  ,$mth_end  ,$dy_end  ,$hh_end,  $mm_end,  $ss_end,  $dt_end,
		     $summary ,$location, false,
		     'tag_vevent',
		     'tag_summary',
		     'tag_dtstart',
		     'tag_dtend',
		     'tag_location',
		     'tag_uid',
		     'tag_dtstamp'
		     );
	break;
      case 'error' :
	$renderer->doc .= "<div class='error'>$yy_start</div>";
	break;
      default:
	$renderer->doc .= "<div class='error'>" . $this->getLang('hcal_invalid_mode') . "</div>";
	break;
      }
      return true;
    }
    return false;
  }

} // CLASS
