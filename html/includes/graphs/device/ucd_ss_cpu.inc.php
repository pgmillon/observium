<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

if ($width > "1000")
{
  $descr_len = 36;
}
else if ($width > "500")
{
  $descr_len = 24;
} else {
  $descr_len = 12;
  $descr_len += round(($width - 250) / 8);
}

if ($nototal) { $descrlen += "2"; $unitlen += "2";}

if ($width > "500")
{
  if (!$noheader)
  {
    $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, $descr_len+5),0,$descr_len+5)."  Now     Min      Max     Avg'";
    $rrd_options .= " COMMENT:'\l'";
  }
} else {
  if (!$noheader)
  {
    $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, $descr_len+5),0,$descr_len+5)."  Now     Min      Max     Avg\l'";
  }
  $nototal = 1;
}


$i = 0;
$colours = "mixed-10b";

$rrd_filename = get_rrd_path($device, "netscaler-stats-tcp.rrd");

$device_state = unserialize($device['device_state']);

$cpu_oids = array('ssCpuRawUser' => array('colour' => 'c02020'),
                  'ssCpuRawNice' => array('colour' => '008f00'),
                  'ssCpuRawSystem' => array('colour' => 'ea8f00'),
                  'ssCpuRawWait' => array('colour' => '1f78b4'),
                  'ssCpuRawInterrupt' => array(),
                  'ssCpuRawSoftIRQ' => array(),
                  'ssCpuRawKernel' => array(),
                  'ssCpuRawIdle' => array('colour' => 'f5f5e5'),
                  );

foreach ($cpu_oids as $stat => $data)
{

  if(isset($device_state['ucd_ss_cpu'][$stat]))
  {

    if ($data['colour'])
    {
      $colour = $data['colour'];
    } else {
      if (!$config['graph_colours'][$colours][$colour_iter]) { $colour_iter = 0; }
      $colour = $config['graph_colours'][$colours][$colour_iter];
      $colour_iter++;
    }

    $rrd_filename = get_rrd_path($device, "ucd_".$stat.".rrd");

    $graph_return['rrds'][] = $rrd_filename;


    $rrd_options .= " DEF:". $stat . "=".$rrd_filename.":value:AVERAGE";
    $totals[]  = $stat.",UN,0," . $stat . ",IF";
    $rrd_options_b .= " CDEF:". $stat . "_perc=".$stat.",total,/,100,*";

    $rrd_optionsc .= " AREA:".$stat."_perc#".$colour.":'".rrdtool_escape(str_replace("ssCpuRaw", "", $stat), $descr_len)."':$bstack";
    $rrd_optionsc .= " GPRINT:".$stat."_perc:LAST:%5.1lf%% GPRINT:".$stat."_perc:MIN:%5.1lf%%";
    $rrd_optionsc .= " GPRINT:".$stat."_perc:MAX:%5.1lf%% GPRINT:".$stat."_perc:AVERAGE:%5.1lf%%\\n";
    $bstack = "STACK";

  }

}

$pluses    = str_repeat(',+', count($totals) - 1);
$totals  = implode(',', $totals);
$rrd_options .= " CDEF:total=" . $totals . $pluses;
$rrd_options .= $rrd_options_b;
$rrd_options .= " HRULE:0#555555";
$rrd_options .= $rrd_optionsc;

// Clean
unset($rrd_multi, $thingX, $plusesX);

// EOF
