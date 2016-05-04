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

$rrd_filename = get_rrd_path($device, "juniperive_users.rrd");

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['descr'] = "Cluster";
$rrd_list[0]['ds'] = "clusterusers";

$rrd_list[1]['filename'] = $rrd_filename;
$rrd_list[1]['descr'] = "Local";
$rrd_list[1]['ds'] = "iveusers";

if ($_GET['debug']) { print_vars($rrd_list); }

$colours = "juniperive";
$nototal = 1;
$unit_text = "Users";
$scale_min = "0";

include("includes/graphs/generic_multi_line.inc.php");

?>
