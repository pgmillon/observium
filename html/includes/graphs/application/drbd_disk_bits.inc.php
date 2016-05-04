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

$ds_in = "dr";
$ds_out = "dw";

$multiplier = "8";
$format = "bytes";

include("includes/graphs/generic_data.inc.php");

// EOF
