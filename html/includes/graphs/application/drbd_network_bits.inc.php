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

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$drbd_rrd = get_rrd_path($device, "app-drbd-".$app['app_instance'].".rrd");

if (is_file($drbd_rrd))
{
  $rrd_filename = $drbd_rrd;
}

$ds_in = "nr";
$ds_out = "ns";

$multiplier = "8";

include("includes/graphs/generic_data.inc.php");

// EOF
