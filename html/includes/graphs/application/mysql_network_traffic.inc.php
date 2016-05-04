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

$mysql_rrd = get_rrd_path($device, "app-mysql-".$app['app_id'].".rrd");

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$multiplier = 8;

$ds_in = "BRd";
$ds_out = "BSt";

include("includes/graphs/generic_data.inc.php");

// EOF
