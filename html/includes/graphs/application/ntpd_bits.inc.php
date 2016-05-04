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

$nototal             = 1;
$ds_in               = "packets_recv";
$ds_out              = "packets_sent";
$graph_title        .= "::packets";
$unit_text           = "Packets";
$colour_line_in      = "330033";
$colour_line_out     = "FF6600";
$colour_area_in      = "AA66AA";
$colour_area_out     = "FFDD88";
$colour_area_in_max  = "CC88CC";
$colour_area_out_max = "FFEFAA";

$ntpdserver_rrd      = get_rrd_path($device, "app-ntpd-server-".$app['app_id'].".rrd");

if (is_file($ntpdserver_rrd))
{
  $rrd_filename = $ntpdserver_rrd;
}

include("includes/graphs/generic_duplex.inc.php");

// EOF
