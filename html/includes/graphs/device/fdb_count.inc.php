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

$rrd = get_rrd_path($device, "fdb_count.rrd");

if (is_file($rrd))
{
  $rrd_filename = $rrd;
}

$ds = 'value';
$colour_area = 'EEEEEE';
$colour_line = '36393D';
$colour_area_max = 'FFEE99';
$unit_text = 'MACs';
$unit_integer = TRUE;
$line_text = 'Count';

include_once('includes/graphs/generic_simplex.inc.php');

//$rrd_options .= " DEF:value=$rrd_filename:fdb:AVERAGE";
//$rrd_options .= " DEF:value_min=$rrd_filename:fdb:MIN";
//$rrd_options .= " DEF:value_max=$rrd_filename:fdb:MAX";
//
//$rrd_options .= " COMMENT:'MACs      Current  Minimum  Maximum  Average\\n'";
//$rrd_options .= " AREA:value#EEEEEE:value";
//$rrd_options .= " LINE1.25:value#36393D:";
//$rrd_options .= " 'GPRINT:value:LAST:%6.2lf ' 'GPRINT:value_min:MIN:%6.2lf '";
//$rrd_options .= " 'GPRINT:value_max:MAX:%6.2lf ' 'GPRINT:value:AVERAGE:%6.2lf\\n'";

// EOF
