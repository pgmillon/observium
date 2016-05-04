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
$scale_max = "100";

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                                 Cur    Max\\n'";

$colour = toner_to_colour($toner['toner_descr']);
if ($colour['left'] == NULL) { $colour['left']="CC0000"; }

$descr = rrdtool_escape($toner['toner_descr'],26);

$background = get_percentage_colours(100-$toner['toner_current']);

$rrd_options .= " DEF:toner" . $toner['toner_id'] . "=".$rrd_filename.":toner:AVERAGE ";

$rrd_options .= " LINE1:toner" . $toner['toner_id'] . "#" . $colour['left'] . ":'" . $descr . "' ";

$rrd_options .= " AREA:toner" . $toner['toner_id' ] . "#" . $background['right'] . ":";
$rrd_options .= " GPRINT:toner" . $toner['toner_id'] . ":LAST:'%5.0lf%%'";
$rrd_options .= " GPRINT:toner" . $toner['toner_id'] . ":MAX:%5.0lf%%\\\\l";

// EOF
