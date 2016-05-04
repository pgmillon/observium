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
$unit_text    = "Count";
$rrd_filename = get_rrd_path($device, "app-nsd-queries.rrd");

$i            = 0;
$array        = array();

$dns_rcode = array('FORMERR', 'NOERROR', 'NOTIMP', 'NXDOMAIN', 'REFUSED', 'SERVFAIL', 'YXDOMAIN');

$colours = $config['graph_colours']['mixed']; # needs moar colours!

foreach ($dns_rcode as $rcode)
{
  $array["rcode$rcode"] = array('descr' => strtoupper($rcode), 'colour' => $colours[(count($array) % count($colours))]);
}

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = $data['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
