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

$colour="CC0000";
$colour_area="ffaaaa";

$sql = "SELECT * FROM `applications-state` WHERE `application_id` = ?";
$app_state = dbFetchRow($sql, array($app['app_id']));
$app_data = unserialize($app_state['app_state']);
$descr = rrdtool_escape($app['app_instance'], $descr_len);

$rrd_filename = get_rrd_path($device, "wmi-app-mssql_".$app['app_instance']."-cpu.rrd");

$rrd_options .= " -u 100 -l 0 ";
$rrd_options .= " COMMENT:'Usage       Current     Average    Maximum\\n'";
$rrd_options .= " DEF:proc=".$rrd_filename.":percproctime:LAST ";
$rrd_options .= " DEF:lastpoll=".$rrd_filename.":lastpoll:LAST ";
$rrd_options .= " CDEF:usage=proc,lastpoll,/,100,* ";

$rrd_options .= " 'AREA:usage#ea8f00:   '";
$rrd_options .= " GPRINT:usage:LAST:'     %5.2lf%%'";
$rrd_options .= " GPRINT:usage:AVERAGE:'   %5.2lf%%'";
$rrd_options .= " GPRINT:usage:MAX:'   %5.2lf%%\\n'";

// EOF
