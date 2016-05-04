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
$unit_text    = "Seconds";
$rrd_filename = get_rrd_path($device, "wmi-app-mssql_".$app['app_instance']."-buffer.rrd");

if (is_file($rrd_filename))
{
  $rrd_list[0]['filename'] = $rrd_filename;
  $rrd_list[0]['descr']    = "Page Lookups per Second";
  $rrd_list[0]['ds']       = "pagelookupssec";

  $rrd_list[1]['filename'] = $rrd_filename;
  $rrd_list[1]['descr']    = "Page Reads per Second";
  $rrd_list[1]['ds']       = "pagereadssec";

  $rrd_list[2]['filename'] = $rrd_filename;
  $rrd_list[2]['descr']    = "Page Writes per Second";
  $rrd_list[2]['ds']       = "pagewritessec";

} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
