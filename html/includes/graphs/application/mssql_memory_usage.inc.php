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

$rrd_filename = get_rrd_path($device, "wmi-app-mssql_".$app['app_instance']."-memory.rrd");

$rrd_options .= " -b 1024 -l 0 ";
$rrd_options .= " COMMENT:'            Current      Average      Maximum\l'";
$rrd_options .= " DEF:used=".$rrd_filename.":totalmemory:AVERAGE ";
$rrd_options .= " DEF:total=".$rrd_filename.":targetmemory:AVERAGE ";
$rrd_options .= " DEF:cache=".$rrd_filename.":cachememory:AVERAGE ";
$rrd_options .= " CDEF:free=total,used,-";
$rrd_options .= " CDEF:usedperc=used,total,/,100,* ";
$rrd_options .= " CDEF:cacheperc=cache,total,/,100,* ";
$rrd_options .= " CDEF:freeperc=100,usedperc,- ";

$rrd_options .= " 'AREA:used#ffaa66:Used   '";
$rrd_options .= " 'GPRINT:used:LAST:%6.2lf%sB   '";
$rrd_options .= " 'GPRINT:used:AVERAGE:%6.2lf%sB   '";
$rrd_options .= " 'GPRINT:used:MAX:%6.2lf%sB'";
$rrd_options .= " 'GPRINT:usedperc:LAST:%3.0lf%%\\n'";
$rrd_options .= " 'AREA:cache#f0e0a0:Cached :STACK'";
$rrd_options .= " 'GPRINT:cache:LAST:%6.2lf%sB   '";
$rrd_options .= " 'GPRINT:cache:AVERAGE:%6.2lf%sB   '";
$rrd_options .= " 'GPRINT:cache:MAX:%6.2lf%sB'";
$rrd_options .= " 'GPRINT:cacheperc:LAST:%3.0lf%%\\n'";
$rrd_options .= " 'LINE1:total#050505:Total'";
$rrd_options .= " 'GPRINT:total:AVERAGE:  %6.2lf%sB\\n'";
$rrd_options .= " 'HRULE:0#000000'";

// EOF
