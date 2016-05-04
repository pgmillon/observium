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

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$colours      = "mixed";
$nototal      = 1;
$unit_text    = "Queries/sec";
$rrd_filename = get_rrd_path($device, "app-nsd-queries.rrd");

$i            = 0;
$array        = array();

if (is_file($rrd_filename))
{
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = 'Requests for AXFR';
  $rrd_list[$i]['ds'] = 'numRequestAXFR';
  $rrd_list[$i]['colour'] = 'FF0000FF';
  $i++;
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
