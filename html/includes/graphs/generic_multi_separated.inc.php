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

include($config['html_dir']."/includes/graphs/common.inc.php");

$graph_return['valid_options'][] = "previous";
$graph_return['valid_options'][] = "total";
$graph_return['valid_options'][] = "trend";

// Here we scale the number of numerical columns shown to make sure we keep the text.

if ($width > 600) {
  $data_show = array('lst', 'avg', 'min', 'max', 'tot');
} elseif ($width > 400) {
  $data_show = array('lst', 'avg', 'max', 'tot');
} elseif ($width > 300) {
  $data_show = array('lst', 'avg', 'max', 'tot');
} else {
  $data_show = array('lst', 'avg', 'max');
}

// Drop total from view if requested not to show
if ($args['nototal'] || $nototal)
{
  if (($key = array_search('tot', $data_show)) !== FALSE)
  {
    unset($data_show[$key]);
  }
}

$data_len = count($data_show) * 8;

// Here we scale the length of the description to make sure we keep the numbers

if ($width > 600) {
  $descr_len = 40;
} elseif ($width > 300) {
  $descr_len = floor(($width + 42) / 8) - $data_len;
} else {
  $descr_len = floor(($width + 42) / 7) - $data_len;
}

// Build the legend headers using the length values previously calculated

if (!isset($unit_text))
{
  if ($format == "octets" || $format == "bytes")
  {
    $units = "Bps";
    $format = "bytes";
    $unit_text = "Bytes/s";
  } else {
    $units = "bps";
    $format = "bits";
    $unit_text = "Bits/s";
  }
}

if ($legend != 'no')
{
  $rrd_options .= " COMMENT:'".rrdtool_escape($unit_text, $descr_len)."'";
  if (in_array("lst", $data_show)) { $rrd_options .= " COMMENT:'   Now'"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " COMMENT:'    Avg'"; }
  if (in_array("min", $data_show)) { $rrd_options .= " COMMENT:'    Min'"; }
  if (in_array("max", $data_show)) { $rrd_options .= " COMMENT:'    Max'"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " COMMENT:'  Total'"; }
  $rrd_options .= " COMMENT:'\\l'";
}

$i = 0;
$rrd_multi = array();
foreach ($rrd_list as $rrd)
{
  if (!$config['graph_colours'][$colours_in][$iter] || !$config['graph_colours'][$colours_out][$iter]) { $iter = 0; }

  $graph_return['rrds'][] = $rrd['filename'];

  $colour_in=$config['graph_colours'][$colours_in][$iter];
  $colour_out=$config['graph_colours'][$colours_out][$iter];

  if ($rrd['colour_area_in']) { $colour_in = $rrd['colour_area_in']; }
  if ($rrd['colour_area_out']) { $colour_out = $rrd['colour_area_out']; }

  $rrd_options .= " DEF:inB".$i."=".$rrd['filename'].":".$rrd['ds_in'].":AVERAGE ";
  $rrd_options .= " DEF:outB".$i."=".$rrd['filename'].":".$rrd['ds_out'].":AVERAGE ";
  $rrd_options .= " CDEF:octets".$i."=inB".$i.",outB".$i.",+";
  $rrd_options .= " CDEF:inbits".$i."=inB".$i.",$multiplier,* ";
  $rrd_options .= " CDEF:outbits".$i."=outB".$i.",$multiplier,*";
  $rrd_options .= " CDEF:outbits".$i."_neg=outbits".$i.",-1,*";
  $rrd_options .= " CDEF:bits".$i."=inbits".$i.",outbits".$i.",+";

  if ($vars['previous'])
  {
    $rrd_options .= " DEF:inB" . $i . "X=".$rrd['filename'].":".$ds_in.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " DEF:outB" . $i . "X=".$rrd['filename'].":".$ds_out.":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:inB" . $i . "X:$period";
    $rrd_options .= " SHIFT:outB" . $i . "X:$period";

    $rrd_multi['in_thingX'][]  = "inB"  . $i . "X,UN,0," . "inB"  . $i . "X,IF";
    $rrd_multi['out_thingX'][] = "outB" . $i . "X,UN,0," . "outB" . $i . "X,IF";
  }

  if (in_array("tot", $data_show))
  {
    $rrd_options .= " VDEF:totinB".$i."=inB".$i.",TOTAL";
    $rrd_options .= " VDEF:totoutB".$i."=outB".$i.",TOTAL";
    $rrd_options .= " VDEF:tot".$i."=octets".$i.",TOTAL";

    $rrd_multi['in_thing'][]  = "inB"  . $i . ",UN,0," . "inB"  . $i . ",IF";
    $rrd_multi['out_thing'][] = "outB" . $i . ",UN,0," . "outB" . $i . ",IF";
  }

  if ($i) { $stack=":STACK"; }

  $rrd_options .= " AREA:inbits".$i."#" . $colour_in . ":'" . rrdtool_escape($rrd['descr'], $descr_len - 3) . " Rx'$stack";

  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:inbits".$i.":LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:inbits".$i.":AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:inbits".$i.":MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:inbits".$i.":MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totinB".$i.":%6.2lf%s".$total_units; }

  $rrd_options .= " COMMENT:'\\l'";
  $rrd_optionsb .= " AREA:outbits".$i."_neg#" . $colour_out . ":".$stack;
  $rrd_options .= "  HRULE:999999999999999#" . $colour_out . ":'" . rrdtool_escape($rrd['descr_out'], $descr_len - 3) . " Tx'";

  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:outbits".$i.":LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:outbits".$i.":AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:outbits".$i.":MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:outbits".$i.":MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totoutB".$i.":%6.2lf%s".$total_units; }

  $rrd_options .= " COMMENT:'\\l'";
  $i++; $iter++;
}

if ($vars['previous'] == "yes")
{
  $in_thingX  = implode(',', $rrd_multi['in_thingX']);
  $out_thingX = implode(',', $rrd_multi['out_thingX']);
  $plusesX    = str_repeat(',+', count($rrd_multi['in_thingX']) - 1);
  $rrd_options .= " CDEF:inBX=" . $in_thingX . $plusesX;
  $rrd_options .= " CDEF:outBX=" . $out_thingX . $plusesX;
  $rrd_options .= " CDEF:octetsX=inBX,outBX,+";
  $rrd_options .= " CDEF:doutBX=outBX,-1,*";
  $rrd_options .= " CDEF:inbitsX=inBX,8,*";
  $rrd_options .= " CDEF:outbitsX=outBX,8,*";
  $rrd_options .= " CDEF:bitsX=inbitsX,outbitsX,+";
  $rrd_options .= " CDEF:doutbitsX=doutBX,8,*";
  $rrd_options .= " VDEF:95thinX=inbitsX,95,PERCENT";
  $rrd_options .= " VDEF:95thoutX=outbitsX,95,PERCENT";
  $rrd_options .= " CDEF:poutX_tmp=doutbitsX,-1,* VDEF:dpoutX_tmp=poutX_tmp,95,PERCENT CDEF:dpoutX_tmp2=doutbitsX,doutbitsX,-,dpoutX_tmp,-1,*,+ VDEF:d95thoutX=dpoutX_tmp2,FIRST";

  $rrd_options  .= " AREA:in".$format."X#99999999";
  $rrd_optionsb .= " AREA:dout".$format."X#99999999";
  $rrd_options  .= " LINE1.25:in".$format."X#666666";
  $rrd_optionsb .= " LINE1.25:dout".$format."X#666666";
}

if (in_array("tot", $data_show))
{
  $in_thing  = implode(',', $rrd_multi['in_thing']);
  $out_thing = implode(',', $rrd_multi['out_thing']);
  $pluses    = str_repeat(',+', count($rrd_multi['in_thing']) - 1);
  $rrd_options .= " CDEF:inB=" . $in_thing . $pluses;
  $rrd_options .= " CDEF:outB=" . $out_thing . $pluses;
  $rrd_options .= " CDEF:octets=inB,outB,+";
  $rrd_options .= " CDEF:doutB=outB,-1,*";
  $rrd_options .= " CDEF:inbits=inB,8,*";
  $rrd_options .= " CDEF:outbits=outB,8,*";
  $rrd_options .= " CDEF:bits=inbits,outbits,+";
  $rrd_options .= " CDEF:doutbits=doutB,8,*";
  $rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
  $rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
  $rrd_options .= " CDEF:pout_tmp=doutbits,-1,* VDEF:dpout_tmp=pout_tmp,95,PERCENT CDEF:dpout_tmp2=doutbits,doutbits,-,dpout_tmp,-1,*,+ VDEF:d95thout=dpout_tmp2,FIRST";

  $rrd_options .= " VDEF:totin=inB,TOTAL";
  $rrd_options .= " VDEF:avein=inbits,AVERAGE";
  $rrd_options .= " VDEF:totout=outB,TOTAL";
  $rrd_options .= " VDEF:aveout=outbits,AVERAGE";
  $rrd_options .= " VDEF:tot=octets,TOTAL";

  $rrd_options .= " COMMENT:' \\l'";

  $rrd_options .= "  HRULE:999999999999999#FFFFFF:'" . rrdtool_escape("Total", $descr_len - 3) . " Rx'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:inbits:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:inbits:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:inbits:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:inbits:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totin:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

  $rrd_options .= "  HRULE:999999999999999#FFFFFF:'" . rrdtool_escape("", $descr_len - 3) . " Tx'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:outbits:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:outbits:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:outbits:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:outbits:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totout:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

  $rrd_options .= "  HRULE:999999999999999#FFFFFF:'" . rrdtool_escape("", $descr_len - 4) . " Agg'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:bits:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:bits:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:bits:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:bits:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:tot:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

  if ($vars['trend'])
  {
    $rrd_options .= " CDEF:smooth_in=inbits,1800,TREND";
    $rrd_options .= " CDEF:predict_in=586400,-7,1800,inbits,PREDICT";
    $rrd_options .= " LINE2:predict_in#FF00FF::dashes=5";

    $rrd_options .= " CDEF:smooth_out=doutbits,1800,TREND";
    $rrd_options .= " CDEF:predict_out=586400,-7,1800,doutbits,PREDICT";
    $rrd_options .= " LINE2:predict_out#FF00FF::dashes=5";
  }
}

if (in_array("tot", $data_show) && $vars['previous'] == "yes")
{
  $rrd_options .= " VDEF:totinX=inBX,TOTAL";
  $rrd_options .= " VDEF:totoutX=outBX,TOTAL";
  $rrd_options .= " VDEF:totX=octetsX,TOTAL";
  $rrd_options .= " COMMENT:' \\l'";

  $rrd_options .= "  HRULE:999999999999999#AAAAAA:'" . rrdtool_escape("Prev Total", $descr_len - 3) . " Rx'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:inbitsX:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:inbitsX:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:inbitsX:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:inbitsX:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totinX:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

  $rrd_options .= "  HRULE:999999999999999#AAAAAA:'" . rrdtool_escape("", $descr_len - 3) . " Tx'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:outbitsX:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:outbitsX:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:outbitsX:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:outbitsX:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totoutX:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

  $rrd_options .= "  HRULE:999999999999999#AAAAAA:'" . rrdtool_escape("", $descr_len - 4) . " Agg'";
  if (in_array("lst", $data_show)) { $rrd_options .= " GPRINT:bitsX:LAST:%6.2lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " GPRINT:bitsX:AVERAGE:%6.2lf%s"; }
  if (in_array("min", $data_show)) { $rrd_options .= " GPRINT:bitsX:MIN:%6.2lf%s"; }
  if (in_array("max", $data_show)) { $rrd_options .= " GPRINT:bitsX:MAX:%6.2lf%s"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " GPRINT:totX:%6.2lf%s".$total_units; }
  $rrd_options .= " COMMENT:'\\l'";

}

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#999999";

// Clean
unset($rrd_multi, $in_thing, $out_thing, $pluses, $in_thingX, $out_thingX, $plusesX);

// EOF
