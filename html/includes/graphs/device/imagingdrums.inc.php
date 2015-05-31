<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " -l 0 -E ";

$iter = "1";
$rrd_options .= " COMMENT:'Imaging Drum level     Cur     Min      Max\\n'";

$drums = array(
  'Cyan' => 'c',
  'Magenta' => 'm',
  'Yellow' => 'y',
  'Black' => 'k',
  );

foreach ($drums as $drum => $letter)
{
  $descr = rrdtool_escape("$drum Drum",16);
  $colour = toner2colour($descr);

  $hostname = gethostbyid($device['device_id']);

  $rrd_filename = get_rrd_path($device, "drum-$letter.rrd");

  $rrd_options .= " DEF:drum$iter=$rrd_filename:drum:AVERAGE";
  $rrd_options .= " LINE2:drum$iter#".$colour['left'].":'" . $descr . "'";
  $rrd_options .= " GPRINT:drum$iter:LAST:'%5.0lf%%'";
  $rrd_options .= " GPRINT:drum$iter:MIN:'%5.0lf%%'";
  $rrd_options .= " GPRINT:drum$iter:MAX:%5.0lf%%\\\\l";

  $iter++;
}

// EOF
