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

// Here we scale the length of the description to make sure we keep the numbers
$data_len = count($data_show) * 8;
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

// EOF
