<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" FASTPATH-ISDP-MIB ");

$isdp_array = snmpwalk_cache_twopart_oid($device, "agentIsdpCache", array(), "FASTPATH-ISDP-MIB", mib_dirs(array('broadcom', 'dell')));

if ($isdp_array)
{
  foreach ($isdp_array as $ifIndex => $port_neighbours)
  {
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $ifIndex));

    foreach ($port_neighbours as $entry_id => $isdp_entry)
    {
      list($isdp_entry['agentIsdpCacheDeviceId']) = explode('(', $isdp_entry['agentIsdpCacheDeviceId']); // Fix for Nexus ISDP neighbors: <hostname>(serial number)

      $remote_device_id = FALSE;
      if (is_valid_hostname($isdp_entry['agentIsdpCacheDeviceId']))
      {
        if (isset($GLOBALS['cache']['discovery-protocols'][$isdp_entry['agentIsdpCacheDeviceId']]))
        {
          // This hostname already checked, skip discover
          $remote_device_id = $GLOBALS['cache']['discovery-protocols'][$isdp_entry['agentIsdpCacheDeviceId']];
        } else {
          $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($isdp_entry['agentIsdpCacheDeviceId'], $isdp_entry['agentIsdpCacheDeviceId']));

          // FIXME do LLDP-code-style hostname overwrite here as well? (see below)
          if (!$remote_device_id && is_valid_hostname($isdp_entry['agentIsdpCacheDeviceId']) && !is_bad_xdp($isdp_entry['agentIsdpCacheDeviceId'], $isdp_entry['agentIsdpCachePlatform']))
          {
            // For now it's a Cisco so CDP discovery is ok
            $remote_device_id = discover_new_device($isdp_entry['agentIsdpCacheDeviceId'], 'xdp', 'ISDP', $device, $port);
          }

          // Cache remote device ID for other protocols
          $GLOBALS['cache']['discovery-protocols'][$isdp_entry['agentIsdpCacheDeviceId']] = $remote_device_id;
        }

        if ($remote_device_id)
        {
          $if = $isdp_entry['agentIsdpCacheDevicePort'];
          $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?", array($if, $if, $remote_device_id));
        } else {
          $remote_port_id = "0";
        }

        if (!is_bad_xdp($isdp_entry['agentIsdpCacheDeviceId']) && $port['port_id'] && $isdp_entry['agentIsdpCacheDeviceId'] && $isdp_entry['agentIsdpCacheDevicePort'])
        {
          $remote_address = $isdp_entry['agentIsdpCacheAddress'];
          if (!get_ip_version($remote_address)) { $remote_address = NULL; }

          discover_link($valid_link, $port['port_id'], 'isdp', $remote_port_id, $isdp_entry['agentIsdpCacheDeviceId'], $isdp_entry['agentIsdpCacheDevicePort'], $isdp_entry['agentIsdpCachePlatform'], $isdp_entry['agentIsdpCacheVersion'], $remote_address);
        }
      } else {
        echo("X");
      }
    }
  }
}

// EOF
