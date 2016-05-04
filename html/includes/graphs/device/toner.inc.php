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

$rrd_options .= " -l 0 -E ";

$iter = "1";
$rrd_options .= " COMMENT:'Toner level            Cur     Min      Max\\n'";
foreach (dbFetchRows("SELECT * FROM toner where device_id = ?", array($device['device_id'])) as $toner)
{
  $colour = toner_to_colour($toner['toner_descr']);

  if ($colour['left'] == NULL)
  {
    // FIXME generic colour function
    switch ($iter)
    {
      case "1":
        $colour['left']= "000000";
        break;
      case "2":
        $colour['left']= "008C00";
        break;
      case "3":
        $colour['left']= "4096EE";
        break;
      case "4":
        $colour['left']= "73880A";
        break;
      case "5":
        $colour['left']= "D01F3C";
        break;
      case "6":
        $colour['left']= "36393D";
        break;
      case "7":
      default:
        $colour['left']= "FF0000";
        unset($iter);
        break;
    }
  }

  $hostname = get_device_by_device_id($toner['device_id']);

  $descr = rrdtool_escape($toner['toner_descr'], 16);
  $rrd_filename = get_rrd_path($device, "toner-" . $toner['toner_index'] . ".rrd");
  $toner_id = $toner['toner_id'];

  $rrd_options .= " DEF:toner$toner_id=$rrd_filename:toner:AVERAGE";
  $rrd_options .= " LINE2:toner$toner_id#".$colour['left'].":'" . $descr . "'";
  $rrd_options .= " GPRINT:toner$toner_id:LAST:'%5.0lf%%'";
  $rrd_options .= " GPRINT:toner$toner_id:MIN:'%5.0lf%%'";
  $rrd_options .= " GPRINT:toner$toner_id:MAX:%5.0lf%%\\\\l";

  $iter++;
}

// EOF
