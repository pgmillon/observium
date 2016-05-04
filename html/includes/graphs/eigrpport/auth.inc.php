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
  $data = dbFetchRow("SELECT * FROM `eigrp_ports` WHERE `eigrp_port_id` = ?", array($vars['id']));

  if (is_numeric($data['device_id']) && ($auth || device_permitted($data['device_id'])))
  {
    $device = device_by_id_cache($data['device_id']);
    $port   = get_port_by_id($data['port_id']);

    $rrd_filename = get_rrd_path($device, "eigrp_port-".$data['eigrp_vpn']."-".$data['eigrp_as']."-".$port['ifIndex'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: EIGRP :: Port :: " . escape_html($port['port_label']);
    $auth = TRUE;
  }
}

// EOF
