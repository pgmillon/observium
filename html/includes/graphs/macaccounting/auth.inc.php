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

if (is_numeric($vars['id']))
{

  $ma = dbFetchRow("SELECT * FROM `mac_accounting` AS M, `ports` AS I, `devices` AS D WHERE M.ma_id = ? AND I.port_id = M.port_id AND I.device_id = D.device_id", array($vars['id']));

  if (OBS_DEBUG) {
    echo("<pre>");
    print_vars($ma);
    echo("</pre>");
  }

  if (is_array($ma))
  {

    if ($auth || port_permitted($ma['port_id']))
    {
      $rrd_filename = get_rrd_path($device, "mac_acc-" . $ma['ifIndex'] . "-" . $ma['vlan_id'] ."-" . $ma['mac'] . ".rrd");

      if (is_file($rrd_filename))
      {
        $port   = get_port_by_id($ma['port_id']);
        $device = device_by_id_cache($port['device_id']);
        $title  = generate_device_link($device);
        $title .= " :: Port  ".generate_port_link($port);
        $title .= " :: Mac Accounting";
        $title .= " :: " . format_mac($ma['mac']);
        $auth   = TRUE;
      } else {
   #     graph_error("file not found");
      }
    } else {
  #    graph_error("unauthenticated");
    }
  } else {
 #   graph_error("entry not found");
  }
} else {
#  graph_error("invalid id");
}
?>
