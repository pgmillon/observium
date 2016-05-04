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

$radio1 = get_rrd_path($device, "wificlients-radio1.rrd");
$radio2 = get_rrd_path($device, "wificlients-radio2.rrd");

if (is_file($radio1))
{
  $radio2_exists = is_file($radio2);

  $rrd_options .= " COMMENT:'                           Cur   Min  Max\\n'";
  $rrd_options .= " DEF:wificlients1=".$radio1.":wificlients:AVERAGE ";
  if ($radio2_exists) {
    $rrd_options .= " LINE1:wificlients1#CC0000:'Clients on Radio1    ' ";
  } else {
    $rrd_options .= " LINE1:wificlients1#CC0000:'Clients              ' ";
  }
  $rrd_options .= " GPRINT:wificlients1:LAST:%3.0lf ";
  $rrd_options .= " GPRINT:wificlients1:MIN:%3.0lf ";
  $rrd_options .= " GPRINT:wificlients1:MAX:%3.0lf\\\l ";
  if ($radio2_exists)
  {
    $rrd_options .= " DEF:wificlients2=".$radio2.":wificlients:AVERAGE ";
    $rrd_options .= " LINE1:wificlients2#008C00:'Clients on Radio2    ' ";
    $rrd_options .= " GPRINT:wificlients2:LAST:%3.0lf ";
    $rrd_options .= " GPRINT:wificlients2:MIN:%3.0lf ";
    $rrd_options .= " GPRINT:wificlients2:MAX:%3.0lf\\\l ";
  }
}

// EOF
