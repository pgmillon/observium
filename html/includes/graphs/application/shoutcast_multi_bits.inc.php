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

$units           = "b";
$total_units     = "B";
$colours_in      = "greens";
$multiplier      = "8";
$colours_out     = "blues";

$nototal         = 1;

$ds_in           = "traf_in";
$ds_out          = "traf_out";

$graph_title     = "Traffic Statistic";

$colour_line_in  = "006600";
$colour_line_out = "000099";
$colour_area_in  = "CDEB8B";
$colour_area_out = "C3D9FF";

// FIXME Not compatible this way with get_rrd_path; as long as no advanced storage is used this will work
// Call get_rrd_path below instead of using $rrddir.
$rrddir          = $config['rrd_dir']."/".$device['hostname'];
$files           = array();
$i               = 0;

if ($handle = opendir($rrddir))
{
  while (false !== ($file = readdir($handle)))
  {
    if ($file != "." && $file != "..")
    {
      if (eregi("app-shoutcast-".$app['app_id'], $file))
      {
        array_push($files, $file);
      }
    }
  }
}

foreach ($files as $id => $file)
{
  $hostname                 = eregi_replace('app-shoutcast-'.$app['app_id'].'-', '', $file);
  $hostname                 = eregi_replace('.rrd', '', $hostname);
  list($host, $port)        = explode('_', $hostname, 2);
  $rrd_filenames[]          = $rrddir."/".$file;
  $rrd_list[$i]['filename'] = $rrddir."/".$file;
  $rrd_list[$i]['descr']    = $host.":".$port;
  $rrd_list[$i]['ds_in']    = $ds_in;
  $rrd_list[$i]['ds_out']   = $ds_out;
  $i++;
}

include("includes/graphs/generic_multi_bits_separated.inc.php");

// EOF
