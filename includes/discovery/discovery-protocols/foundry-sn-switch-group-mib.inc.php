<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo(" FOUNDRY-SN-SWITCH-GROUP-MIB ");

$fdp_array = snmpwalk_cache_twopart_oid($device, "snFdpCacheEntry", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

if ($fdp_array)
{
  unset($fdp_links);
  foreach (array_keys($fdp_array) as $key)
  {
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $key));
    $fdp_if_array = $fdp_array[$key];
    foreach (array_keys($fdp_if_array) as $entry_key)
    {
      $fdp = $fdp_if_array[$entry_key];
      $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDeviceId']));

      // FIXME do LLDP-code-style hostname overwrite here as well? (see below)

      if (!$remote_device_id && is_valid_hostname($fdp['snFdpCacheDeviceId']) && !is_bad_xdp($fdp['snFdpCacheDeviceId']))
      {
        $remote_device_id = discover_new_device($fdp['snFdpCacheDeviceId'], 'xdp', 'FDP', $device, $port);
      }

      if ($remote_device_id)
      {
        $if = $fdp['snFdpCacheDevicePort'];
        $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?", array($if, $if, $remote_device_id));
      } else {
        $remote_port_id = "0";
      }

      if (!is_bad_xdp($fdp['snFdpCacheDeviceId']))
      {
        discover_link($valid_link, $port['port_id'], $fdp['snFdpCacheVendorId'], $remote_port_id, $fdp['snFdpCacheDeviceId'], $fdp['snFdpCacheDevicePort'], $fdp['snFdpCachePlatform'], $fdp['snFdpCacheVersion']);
      }
    }
  }
}

// EOF
