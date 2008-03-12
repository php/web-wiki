<?php
//setlocale(LC_ALL, 'German_Germany.1252',"deu_deu",'de_DE@euro', 'de_DE', 'de', 'ge');
function hcal_parseCommand($match) {
# print $match."\n";
  $start_yy = $start_mth = $start_day = $start_hh = $start_min = $start_sec  = '';
  $end_yy   = $end_mth   = $end_day   = $end_hh   = $end_min   = $end_sec    = '';
  if (strpos($match,'hcali') != 0) {
    $match = html_entity_decode(substr($match, 8, -2));
    $inlineentry = true;
  } else {
    $match = html_entity_decode(substr($match, 7, -2));
    $inlineentry = false;
  }
  @list($time_info,$summary,$location) = explode('|',$match,3);
  @list($start_info,$end_info) = explode(';',$time_info,2);
  @list($start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data, $err) = hcal_parseEntry($start_info);
  if (!isset($err)) {
    if (isset($end_info)) {
      @list($end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, $err) = hcal_parseEntry($end_info);
      if (!isset($end_data)) {
	$end_data = strtotime($start_yy.'/'.$start_mth.'/'.$start_day.' '.$end_hh.':'.$end_min.':'.$end_sec);
      }
    } 
  }
  return array($summary, $location,
	       $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
	       $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
	       $err);
}
function hcal_parseEntry($time_info) {
  $times = array();
  if (!preg_match('/(\d{4}\/\d{2}\/\d{2})(\[\d{2}:\d{2}:\d{2}\])/',$time_info)) {
    if (preg_match('/(\d{4}\/\d{2}\/\d{2})(\[\d{2}:\d{2}\])/',$time_info)) {
      $time_info = str_replace(']',':00]',$time_info);
    } else {
      $err = "Wrong time format";
    }
  }
  if (preg_match('/(\d{4}\/\d{2}\/\d{2})(\[\d{2}:\d{2}:\d{2}\])?/',$time_info,$times)) {
    return hcal_parseDateTimeEntry($times);
  } else if (preg_match('/(\[\d{2}:\d{2}:\d{2}\])?/',$time_info,$times)) {
    return hcal_parseTimeEntry($times);
  }
  $time_yy = $time_mth = $time_day = $time_hh = $time_min = $time_sec = '';
  return array($time_yy,$time_mth,$time_day,$time_hh,$time_min,$time_sec);
}
function hcal_parseDateTimeEntry($times) {
  list(,$time_date,$time_time) = $times;
  if (!isset($time_date)) { $err = 'No Time date';}
  list($time_yy,$time_mth,$time_day) = explode('/',$time_date);
  $time = strtotime($time_date);
  if (isset($time_time)) {
    $time_time = str_replace('[',null,$time_time);
    $time_time = str_replace(']',null,$time_time);
    @list($time_hh,$time_min,$time_sec) = explode(':',$time_time);
    $time = strtotime($time_date." ".$time_time);
  }
  return array($time_yy,$time_mth,$time_day,$time_hh,$time_min,$time_sec, $time, $err);
}
function hcal_parseTimeEntry($times) {
  @list($time_time) = $times;
  $err = null;
  if (isset($time_time)) {
    $time_time = str_replace('[',null,$time_time);
    $time_time = str_replace(']',null,$time_time);
    @list($time_hh,$time_min,$time_sec) = explode(':',$time_time);
  }
  $time_yy = $time_mth = $time_day = '';
  return array($time_yy,$time_mth,$time_day,$time_hh,$time_min,$time_sec,null,$err);
}

/*
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02[18:00:00]|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02[18:00:00];[19:00:00]|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02[18:00:00];2008/07/03[19:00:00]|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02[18:00];2008/07/03[19:00]|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
@list($summary, $location,
      $start_yy,$start_mth,$start_day,$start_hh,$start_min,$start_sec,$start_data,
      $end_yy,$end_mth,$end_day,$end_hh,$end_min,$end_sec,$end_data, 
      $err) = hcal_parseCommand("{{hcal>2008/07/02;2008/07/03|Summary|Location}}");
print $err." ";
print date('d.F Y H:i:s',$start_data)." ";
print date('d.F Y H:i:s',$end_data)."\n";
*/
