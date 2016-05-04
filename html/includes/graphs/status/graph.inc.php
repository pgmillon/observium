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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                         Last     Max\\n'";

$rrd_options .= " DEF:status=$rrd_filename:status:AVERAGE";
$rrd_options .= " LINE1.5:status#cc0000:'" . rrdtool_escape($status['status_descr'],20)."'";
$rrd_options .= " GPRINT:status:LAST:%3.0lf";
$rrd_options .= " GPRINT:status:MAX:%3.0lf\\\\l";

// EOF
