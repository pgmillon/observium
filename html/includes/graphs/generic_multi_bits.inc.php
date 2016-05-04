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

// FIXME. This file unused

// Draws aggregate bits graph from multiple RRDs
// Variables : colour_[line|area]_[in|out], rrd_filenames

include($config['html_dir']."/includes/graphs/common.inc.php");

$i = 0;
$rrd_multi = array();
foreach ($rrd_filenames as $key => $rrd_filename)
{
  if ($rrd_inverted[$key]) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }

  $rrd_options .= " DEF:".$in."octets" . $i . "=".$rrd_filename.":".$ds_in.":AVERAGE";
  $rrd_options .= " DEF:".$out."octets" . $i . "=".$rrd_filename.":".$ds_out.":AVERAGE";

  $rrd_multi['in_thing'][]  = "inoctets" .  $i . ",UN,0," . "inoctets" .  $i . ",IF";
  $rrd_multi['out_thing'][] = "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";

  if ($_GET['previous'])
  {
    $rrd_options .= " DEF:".$in."octets" . $i . "X=".$rrd_filename.":".$ds_in.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:".$out."octets" . $i . "X=".$rrd_filename.":".$ds_out.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$in."octets" . $i . "X:$period";
    $rrd_options .= " SHIFT:".$out."octets" . $i . "X:$period";

    $rrd_multi['in_thingX'][]  = "inoctets" .  $i . "X,UN,0," . "inoctets" .  $i . "X,IF";
    $rrd_multi['out_thingX'][] = "outoctets" . $i . "X,UN,0," . "outoctets" . $i . "X,IF";
  }
  $i++;
}

if ($i)
{
  if ($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $in_thing  = implode(',', $rrd_multi['in_thing']);
  $out_thing = implode(',', $rrd_multi['out_thing']);
  $pluses    = str_repeat(',+', count($rrd_multi['in_thing']) - 1);
  $rrd_options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $rrd_options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
  $rrd_options .= " CDEF:inbits=inoctets,8,*";
  $rrd_options .= " CDEF:outbits=outoctets,8,*";
  $rrd_options .= " CDEF:doutbits=doutoctets,8,*";
  $rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
  $rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
  $rrd_options .= " CDEF:pout_tmp=doutbits,-1,* VDEF:dpout_tmp=pout_tmp,95,PERCENT CDEF:dpout_tmp2=doutbits,doutbits,-,dpout_tmp,-1,*,+ VDEF:d95thout=dpout_tmp2,FIRST";

  if ($vars['previous'] == "yes")
  {
    $in_thingX  = implode(',', $rrd_multi['in_thingX']);
    $out_thingX = implode(',', $rrd_multi['out_thingX']);
    $plusesX    = str_repeat(',+', count($rrd_multi['in_thingX']) - 1);
    $rrd_options .= " CDEF:".$in."octetsX=" . $in_thingX . $plusesX;
    $rrd_options .= " CDEF:".$out."octetsX=" . $out_thingX . $plusesX;
    $rrd_options .= " CDEF:doutoctetsX=outoctetsX,-1,*";
    $rrd_options .= " CDEF:inbitsX=inoctetsX,8,*";
    $rrd_options .= " CDEF:outbitsX=outoctetsX,8,*";
    $rrd_options .= " CDEF:doutbitsX=doutoctetsX,8,*";
    $rrd_options .= " VDEF:95thinX=inbitsX,95,PERCENT";
    $rrd_options .= " VDEF:95thoutX=outbitsX,95,PERCENT";
    $rrd_options .= " CDEF:poutX_tmp=doutbitsX,-1,* VDEF:dpoutX_tmp=poutX_tmp,95,PERCENT CDEF:dpoutX_tmp2=doutbitsX,doutbitsX,-,dpoutX_tmp,-1,*,+ VDEF:d95thoutX=dpoutX_tmp2,FIRST";
  }

  if ($legend == 'no' || $legend == '1')
  {
    $rrd_options .= " AREA:inbits#".$colour_area_in.":";
    $rrd_options .= " LINE1.25:inbits#".$colour_line_in.":";
    $rrd_options .= " AREA:doutbits#".$colour_area_out.":";
    $rrd_options .= " LINE1.25:doutbits#".$colour_line_out.":";
  } else {
    $rrd_options .= " AREA:inbits#".$colour_area_in.":";
    $rrd_options .= " COMMENT:'bps      Now       Avg      Max      95th %\\n'";
    $rrd_options .= " LINE1.25:inbits#".$colour_line_in.":In\ ";
    $rrd_options .= " GPRINT:inbits:LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
    $rrd_options .= " GPRINT:inbits:MAX:%6.2lf%s";
    $rrd_options .= " GPRINT:95thin:%6.2lf%s\\\\n";
    $rrd_options .= " AREA:doutbits#".$colour_area_out.":";
    $rrd_options .= " LINE1.25:doutbits#".$colour_line_out.":Out";
    $rrd_options .= " GPRINT:outbits:LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
    $rrd_options .= " GPRINT:outbits:MAX:%6.2lf%s";
    $rrd_options .= " GPRINT:95thout:%6.2lf%s\\\\n";
  }

  $rrd_options .= " LINE1:95thin#aa0000";
  $rrd_options .= " LINE1:d95thout#aa0000";

  if ($vars['previous'] == "yes")
  {
    $rrd_options .= " AREA:inbitsX#9999966:";
    $rrd_options .= " AREA:doutbitsX#99999966:";
  }

}

#$rrd_options .= " HRULE:0#999999";

// Clean
unset($rrd_multi, $in_thing, $out_thing, $pluses, $in_thingX, $out_thingX, $plusesX);

// EOF
