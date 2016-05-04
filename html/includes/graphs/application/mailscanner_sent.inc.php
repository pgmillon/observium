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

$ds_in               = "msg_recv";
$ds_out              = "msg_sent";

$graph_titel        .= "::messages";
$unit_text           = "Messages/sec";

$colour_line_in      = "008800FF";
$colour_line_out     = "000088FF";
$colour_area_in      = "CEFFCE66";
$colour_area_out     = "CECEFF66";
$colour_area_in_max  = "CC88CC";
$colour_area_out_max = "FFEFAA";

$mailscanner_rrd     = get_rrd_path($device, "app-mailscannerV2-" . $app['app_id'] . ".rrd");

if (is_file($mailscanner_rrd))
{
  $rrd_filename = $mailscanner_rrd;
}

include("includes/graphs/generic_duplex.inc.php");

// EOF
