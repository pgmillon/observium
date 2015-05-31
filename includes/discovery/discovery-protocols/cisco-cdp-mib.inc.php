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

echo(" CISCO-CDP-MIB ");

$cdp_array = snmpwalk_cache_twopart_oid($device, "cdpCache", array(), "CISCO-CDP-MIB", mib_dirs('cisco'));

if ($cdp_array)
{
  foreach (array_keys($cdp_array) as $key)
  {
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $key));
    $cdp_if_array = $cdp_array[$key];
    foreach (array_keys($cdp_if_array) as $entry_key)
    {
      $cdp = $cdp_if_array[$entry_key];
      list($cdp['cdpCacheDeviceId']) = explode('(', $cdp['cdpCacheDeviceId']); // Fix for Nexus CDP neighbors: <hostname>(serial number)

      if (is_valid_hostname($cdp['cdpCacheDeviceId']))
      {
        $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($cdp['cdpCacheDeviceId'], $cdp['cdpCacheDeviceId']));

        // FIXME do LLDP-code-style hostname overwrite here as well? (see below)

        if (!$remote_device_id && is_valid_hostname($cdp['cdpCacheDeviceId']) && !is_bad_xdp($cdp['cdpCacheDeviceId']))
        {
          $remote_device_id = discover_new_device($cdp['cdpCacheDeviceId'], 'xdp', 'CDP', $device, $port);
        }

        if ($remote_device_id)
        {
          $if = $cdp['cdpCacheDevicePort'];
          $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?", array($if, $if, $remote_device_id));
        } else {
          $remote_port_id = "0";
        }

        if (!is_bad_xdp($cdp['cdpCacheDeviceId']) && $port['port_id'] && $cdp['cdpCacheDeviceId'] && $cdp['cdpCacheDevicePort'])
        {
          discover_link($valid_link, $port['port_id'], 'cdp', $remote_port_id, $cdp['cdpCacheDeviceId'], $cdp['cdpCacheDevicePort'], $cdp['cdpCachePlatform'], $cdp['cdpCacheVersion']);
        }
      }
      else
      {
        echo("X");
      }
    }
  }
}

// EOF
