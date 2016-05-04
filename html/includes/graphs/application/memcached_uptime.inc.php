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

$scale_min = "0";

include("memcached.inc.php");
include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " DEF:uptime=".$rrd_filename.":uptime:AVERAGE";
$rrd_options .= " CDEF:cuptime=uptime,86400,/";
$rrd_options .= " 'COMMENT:Days      Current  Minimum  Maximum  Average\\n'";
$rrd_options .= " AREA:cuptime#EEEEEE:Uptime";
$rrd_options .= " LINE1.25:cuptime#36393D:";
$rrd_options .= " GPRINT:cuptime:LAST:%6.2lf  GPRINT:cuptime:AVERAGE:%6.2lf";
$rrd_options .= " GPRINT:cuptime:MAX:%6.2lf  'GPRINT:cuptime:AVERAGE:%6.2lf\\n'";

// EOF
