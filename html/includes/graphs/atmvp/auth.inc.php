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

if ($_GET['id'] && is_numeric($_GET['id'])) { $atm_vp_id = $_GET['id']; }

$vp = dbFetchRow("SELECT * FROM `juniAtmVp` as J, `ports` AS I, `devices` AS D WHERE J.juniAtmVp_id = ? AND I.port_id = J.port_id AND I.device_id = D.device_id", array($atm_vp_id));

if ($auth || port_permitted($vp['port_id']))
{
  $port   = $vp;
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  ".generate_port_link($port);
  $title .= " :: VP ".$vp['vp_id'];
  $auth = TRUE;
  $rrd_filename = get_rrd_path($device, "vp-" . $vp['ifIndex'] . "-".$vp['vp_id'].".rrd");
}

?>
