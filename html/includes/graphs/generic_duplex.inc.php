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

// Draw generic duplex graph
// args: ds_in, ds_out, rrd_filename, bg, legend, from, to, width, height, inverse, $percentile

include($config['html_dir']."/includes/graphs/common.inc.php");

$length = "10";

if (!isset($percentile)) { $length += "2"; }

if (!isset($out_text)) { $out_text = "Out"; }
if (!isset($in_text)) { $in_text = "In"; }

$unit_text = str_pad(truncate($unit_text,$length),$length);
$in_text   = str_pad(truncate($in_text,$length),$length);
$out_text  = str_pad(truncate($out_text,$length),$length);

// Alternative style
if ($graph_style == 'mrtg')
{
  $out_scale = 1;
  $out_area  = 'LINE1';

} else {
  $out_scale = -1;
  $out_area  = 'AREA';
}

if (!$noheader)
{
  $rrd_options .= " COMMENT:'".$unit_text."      Last      Avg      Max";
  if ($percentile)
  {
    $rrd_options .= "      ".$percentile."th %";
  }
  $rrd_options .= " \\n'";
}

if (isset($defs))
{
  $rrd_options .= $defs;
} else {
  $rrd_options .= " DEF:".$out."=".$rrd_filename.":".$ds_out.":AVERAGE";
  $rrd_options .= " DEF:".$in."=".$rrd_filename.":".$ds_in.":AVERAGE";
  $rrd_options .= " DEF:".$out."_max=".$rrd_filename.":".$ds_out.":MAX";
  $rrd_options .= " DEF:".$in."_max=".$rrd_filename.":".$ds_in.":MAX";
}

$rrd_options .= " CDEF:dout_max=out_max,".$out_scale.",*";
$rrd_options .= " CDEF:dout=out,".$out_scale.",*";
$rrd_options .= " CDEF:both=in,out,+";

// Unknown data
$rrd_options .= " CDEF:alloctets=".$out.",".$in.",+";
$rrd_options .= " CDEF:wrongin=alloctets,UN,INF,UNKN,IF";
$rrd_options .= " CDEF:wrongout=wrongin,".$out_scale.",*";

if ($print_total)
{
  $rrd_options .= " VDEF:totin=in,TOTAL";
  $rrd_options .= " VDEF:totout=out,TOTAL";
  $rrd_options .= " VDEF:tot=both,TOTAL";
}
if ($percentile)
{
  $rrd_options .= " VDEF:percentile_in=in,".$percentile.",PERCENT";
  $rrd_options .= " VDEF:percentile_out=out,".$percentile.",PERCENT";
  $rrd_options .= " CDEF:pout_tmp=dout,".$out_scale.",* VDEF:dpout_tmp=pout_tmp,".$percentile.",PERCENT CDEF:dpout_tmp2=dout,dout,-,dpout_tmp,".$out_scale.",*,+ VDEF:dpercentile_out=dpout_tmp2,FIRST";
}
if ($graph_max)
{
  $rrd_options .= " AREA:in_max#".$colour_area_in_max.":";
  $rrd_options .= " ".$out_area.":dout_max#".$colour_area_out_max.":";
}

if ($vars['previous'] == "yes")
{
  $rrd_options .= " DEF:".$out."X=".$rrd_filename.":".$ds_out.":AVERAGE:start=".$prev_from.":end=".$from;
  $rrd_options .= " DEF:".$in."X=".$rrd_filename.":".$ds_in.":AVERAGE:start=".$prev_from.":end=".$from;
  $rrd_options .= " DEF:".$out."_maxX=".$rrd_filename.":".$ds_out.":MAX:start=".$prev_from.":end=".$from;
  $rrd_options .= " DEF:".$in."_maxX=".$rrd_filename.":".$ds_in.":MAX:start=".$prev_from.":end=".$from;
  $rrd_options .= " SHIFT:".$out."X:$period";
  $rrd_options .= " SHIFT:".$in."X:$period";
  $rrd_options .= " SHIFT:".$out."_maxX:$period";
  $rrd_options .= " SHIFT:".$in."_maxX:$period";
  $rrd_options .= " CDEF:dout_maxX=out_maxX,".$out_scale.",*";
  $rrd_options .= " CDEF:doutX=outX,".$out_scale.",*";
  $rrd_options .= " CDEF:bothX=inX,outX,+";
  if ($print_total)
  {
    $rrd_options .= " VDEF:totinX=inX,TOTAL";
    $rrd_options .= " VDEF:totoutX=outX,TOTAL";
    $rrd_options .= " VDEF:totX=bothX,TOTAL";
  }
  if ($percentile)
  {
    $rrd_options .= " VDEF:percentile_inX=inX,".$percentile.",PERCENT";
    $rrd_options .= " VDEF:percentile_outX=outX,".$percentile.",PERCENT";
    $rrd_options .= " CDEF:poutX_tmp=doutX,".$out_scale.",* VDEF:dpoutX_tmp=poutX_tmp,".$percentile.",PERCENT CDEF:dpoutX_tmp2=doutX,doutX,-,dpoutX_tmp,".$out_scale.",*,+ VDEF:dpercentile_outX=dpoutX_tmp2,FIRST";
  }
  if ($graph_max)
  {
    $rrd_options .= " AREA:in_max#".$colour_area_in_max.":";
    $rrd_options .= " ".$out_area.":dout_max#".$colour_area_out_max.":";
  }
}

$rrd_options .= " AREA:in#".$colour_area_in;
if ($graph_style != 'mrtg')
{
  $rrd_options .= " LINE1.25:in#".$colour_line_in;
}
$rrd_options .= ":'".$in_text."'";

$rrd_options .= " GPRINT:in:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:in:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:in_max:MAX:%6.2lf%s";

if ($percentile)
{
  $rrd_options .= " GPRINT:percentile_in:%6.2lf%s";
}

$rrd_options .= " COMMENT:'\\n'";
if ($graph_style != 'mrtg')
{
  $rrd_options .= " AREA:dout#".$colour_area_out.":";
}
$rrd_options .= " LINE1.25:dout#".$colour_line_out.":'".$out_text."'";

$rrd_options .= " GPRINT:out:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:out:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:out_max:MAX:%6.2lf%s";

if ($percentile)
{
  $rrd_options .= " GPRINT:percentile_out:%6.2lf%s";
}

$rrd_options .= " COMMENT:\\\\n";

if ($print_total)
{
  $rrd_options .= " GPRINT:tot:'Total %6.2lf%s'";
  $rrd_options .= " GPRINT:totin:'(In %6.2lf%s'";
  $rrd_options .= " GPRINT:totout:'Out %6.2lf%s)\\\\l'";
}

if ($percentile)
{
  $rrd_options .= " LINE1:percentile_in#aa0000";
  $rrd_options .= " LINE1:dpercentile_out#aa0000";
}

if ($vars['previous'] == "yes")
{
  $rrd_options .= " LINE1.25:in".$format."X#666666:'Prev In \\\\n'";
  $rrd_options .= " AREA:in".$format."X#99999966:";
  $rrd_options .= " LINE1.25:dout".$format."X#444466:'Prev Out'";
  if ($graph_style != 'mrtg')
  {
    $rrd_options .= " AREA:dout".$format."X#99444466:";
  }
} else {
  $rrd_options .= " AREA:wrongin#FFF2F2";
  $rrd_options .= " AREA:wrongout#FFF2F2";
}

$rrd_options .= " HRULE:0#999999";
//if ($graph_style == 'mrtg')
//{
//  $midnight = strtotime('today midnight');
//  for ($i = 1; $i <= 2; $i++)
//  {
//    $rrd_options .= " VRULE:${midnight}#FF0000";
//    $midnight -= 86400;
//  }
//}

// EOF
