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

$colour_iter = 0;

foreach ($rrd_list as $i => $rrd)
{
  if ($rrd['colour'])
  {
    $colour = $rrd['colour'];
  } else {
    if (!isset($config['graph_colours'][$colours][$colour_iter])) { $colour_iter = 0; }
    $colour = $config['graph_colours'][$colours][$colour_iter];
    $colour_iter++;
  }

  $ds = $rrd['ds'];
  $filename = $rrd['filename'];

  $descr = rrdtool_escape($rrd['descr'], $descr_len);

  $id = "ds".$i;

  $rrd_options .= " DEF:".$id."=$filename:$ds:AVERAGE";

  if ($simple_rrd)
  {
    $rrd_options .= " CDEF:".$id."min=".$id." ";
    $rrd_options .= " CDEF:".$id."max=".$id." ";
  } else {
    $rrd_options .= " DEF:".$id."min=$filename:$ds:MIN";
    $rrd_options .= " DEF:".$id."max=$filename:$ds:MAX";
  }

  if ($rrd['invert'])
  {
    $rrd_options .= " CDEF:".$id."i=".$id.",-1,*";
    $rrd_optionsb .= " LINE1.25:".$id."i#".$colour.":'$descr'";
    if (!empty($rrd['areacolour'])) { $rrd_optionsb .= " AREA:".$id."i#" . $rrd['areacolour']; }
  } else {
    $rrd_optionsb .= " LINE1.25:".$id."#".$colour.":'$descr'";
    if (!empty($rrd['areacolour'])) { $rrd_optionsb .= " AREA:".$id."#" . $rrd['areacolour']; }
  }

  if (in_array("lst", $data_show)) { $rrd_optionsb .= " GPRINT:".$id.":LAST:%6.1lf%s"; }
  if (in_array("avg", $data_show)) { $rrd_optionsb .= " GPRINT:".$id.":AVERAGE:%6.1lf%s"; }
  if (in_array("min", $data_show)) { $rrd_optionsb .= " GPRINT:".$id."min:MIN:%6.1lf%s"; }
  if (in_array("max", $data_show)) { $rrd_optionsb .= " GPRINT:".$id."max:MAX:%6.1lf%s"; }

  $rrd_optionsb .= " COMMENT:'\\l'";
  #$colour_iter++;

}

$rrd_options .= $rrd_optionsb;
$rrd_options .= " HRULE:0#555555";

// EOF
