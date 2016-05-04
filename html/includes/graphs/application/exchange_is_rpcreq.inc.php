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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$colours      = "mixed";
$nototal      = 1;
$unit_text    = "Requests";
$rrd_filename = get_rrd_path($device, "wmi-app-exchange-is.rrd");

if (is_file($rrd_filename))
{
  $rrd_list[0]['filename'] = $rrd_filename;
  $rrd_list[0]['descr']    = "RPC Requests";
  $rrd_list[0]['ds']       = "rpcrequests";

  $rrd_list[1]['filename'] = $rrd_filename;
  $rrd_list[1]['descr']    = "RPC Average Latency";
  $rrd_list[1]['ds']       = "rpcavglatency";

} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
