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
    if (!$nototal) { $rrd_options .= " COMMENT:'Total      '"; }
    $rrd_options .= " COMMENT:'\l'";
  }
} else {
  if (!$noheader)
  {
    $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, $descr_len+5),0,$descr_len+5)."  Now     Min      Max     Avg\l'";
  }
  $nototal = 1;
}

$colour_iter = 0;
$rrd_multi = array();
foreach ($rrd_list as $i => $rrd)
{
  if ($rrd['colour'])
  {
    $colour = $rrd['colour'];
  } else {
    if (!$config['graph_colours'][$colours][$colour_iter]) { $colour_iter = 0; }
    $colour = $config['graph_colours'][$colours][$colour_iter];
    $colour_iter++;
  }

  $rrd_options .= " DEF:".$rrd['ds'].$i."=".$rrd['filename'].":".$rrd['ds'].":AVERAGE ";

  if ($simple_rrd)
  {
    $rrd_options .= " CDEF:".$rrd['ds'].$i."min=".$rrd['ds'].$i." ";
    $rrd_options .= " CDEF:".$rrd['ds'].$i."max=".$rrd['ds'].$i." ";
  } else {
    $rrd_options .= " DEF:".$rrd['ds'].$i."min=".$rrd['filename'].":".$rrd['ds'].":MIN ";
    $rrd_options .= " DEF:".$rrd['ds'].$i."max=".$rrd['filename'].":".$rrd['ds'].":MAX ";
  }

  if ($_GET['previous'])
  {
    $rrd_options .= " DEF:".$i . "X=".$rrd['filename'].":".$rrd['ds'].":AVERAGE:start=".$prev_from.":end=".$from;
    $rrd_options .= " SHIFT:".$i . "X:$period";

    $rrd_multi['thingX'][] = $i . "X,UN,0," . $i . "X,IF";
  }

  // Suppress totalling?
  if (!$nototal)
  {
    $rrd_options .= " VDEF:tot".$rrd['ds'].$i."=".$rrd['ds'].$i.",TOTAL";
  }

  # if we've been passed a multiplier we must make a CDEF based on it!
  $g_defname = $rrd['ds'];
  if (is_numeric($multiplier))
  {
    $g_defname = $rrd['ds'] . "_cdef";
    $rrd_options .= " CDEF:" . $g_defname . $i . "=" . $rrd['ds'] . $i . "," . $multiplier . ",*";
    $rrd_options .= " CDEF:" . $g_defname . $i . "min=" . $rrd['ds'] . $i . "min," . $multiplier . ",*";
    $rrd_options .= " CDEF:" . $g_defname . $i . "max=" . $rrd['ds'] . $i . "max," . $multiplier . ",*";

  // If we've been passed a divider (divisor!) we make a CDEF for it.
  } elseif (is_numeric($divider))
  {
    $g_defname = $rrd['ds'] . "_cdef";
    $rrd_options .= " CDEF:" . $g_defname . $i . "=" . $rrd['ds'] . $i . "," . $divider . ",/";
    $rrd_options .= " CDEF:" . $g_defname . $i . "min=" . $rrd['ds'] . $i . "min," . $divider . ",/";
    $rrd_options .= " CDEF:" . $g_defname . $i . "max=" . $rrd['ds'] . $i . "max," . $divider . ",/";
  }

  // Are our text values related to the multiplier/divisor or not?
  if (isset($text_orig) && $text_orig)
  {
    $t_defname = $rrd['ds'];
  } else {
    $t_defname = $g_defname;
  }

  if ($rrd['invert'])
  {
    $rrd_options .= " CDEF:".$g_defname.$i."i=".$g_defname.$i.",-1,*";
    $rrd_optionsc .= " AREA:".$g_defname.$i."i#".$colour.":'".rrdtool_escape($rrd['descr'], $descr_len)."'".$cstack;
    $rrd_optionsc .= " GPRINT:".$t_defname.$i.":LAST:%5.1lf%s GPRINT:".$t_defname.$i."min:MIN:%5.1lf%s";
    $rrd_optionsc .= " GPRINT:".$t_defname.$i."max:MAX:%5.1lf%s GPRINT:".$t_defname.$i.":AVERAGE:%5.1lf%s";
    $cstack = ":STACK";

    if (!$nototal) { $rrd_optionsc .= " GPRINT:tot".$rrd['ds'].$i.":%5.2lf%s".rrdtool_escape($total_units).""; }
    $rrd_optionsc .= "'\\n' COMMENT:'\\n'";
  } else {
    $rrd_optionsb .= " AREA:".$g_defname.$i."#".$colour.":'".rrdtool_escape($rrd['descr'], $descr_len)."'".$bstack;
    $rrd_optionsb .= " GPRINT:".$t_defname.$i.":LAST:%5.1lf%s GPRINT:".$t_defname.$i."min:MIN:%5.1lf%s";
    $rrd_optionsb .= " GPRINT:".$t_defname.$i."max:MAX:%5.1lf%s GPRINT:".$t_defname.$i.":AVERAGE:%5.1lf%s";
    $bstack = ":STACK";

    if (!$nototal) { $rrd_optionsb .= " GPRINT:tot".$rrd['ds'].$i.":%5.2lf%s".rrdtool_escape($total_units).""; }
    $rrd_optionsb .= "'\\n' COMMENT:'\\n'";
  }
}

if ($vars['previous'] == "yes")
{
  $thingX  = implode(',', $rrd_multi['thingX']);
  $plusesX = str_repeat(',+', count($rrd_multi['thingX']) - 1);
  if (is_numeric($multiplier))
  {
    $rrd_options .= " CDEF:X=" . $thingX . $plusesX.",".$multiplier. ",*";
  }
  else if (is_numeric($divider))
  {
    $rrd_options .= " CDEF:X=" . $thingX . $plusesX.",".$divider. ",/";
  } else {
    $rrd_options .= " CDEF:X=" . $thingX . $plusesX;
  }

  $rrd_options .= " AREA:X#99999999:";
  $rrd_options .= " LINE1.25:X#666666:";

}

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#555555";
$rrd_options .= $rrd_optionsc;

// Clean
unset($rrd_multi, $thingX, $plusesX);

// EOF
