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

echo(" CISCO-CDP-MIB ");

$cdp_array = snmpwalk_cache_twopart_oid($device, "cdpCache", array(), "CISCO-CDP-MIB", mib_dirs('cisco'));

// If we get timeout error and device has 'CISCO-FLASH-MIB', sleep and try re-walk
if ($GLOBALS['snmp_status'] === FALSE && $GLOBALS['snmp_error_code'] == 1002 && is_device_mib($device, 'CISCO-FLASH-MIB'))
{
  print_debug('Try to re-walk "CISCO-CDP-MIB"..');
  sleep(5); // Additional sleep, see comments in includes/discovery/storage/cisco-flash-mib-inc.php
  $cdp_array = snmpwalk_cache_twopart_oid($device, "cdpCache", array(), "CISCO-CDP-MIB", mib_dirs('cisco'));
}

if ($cdp_array)
{
  foreach ($cdp_array as $ifIndex => $port_neighbours)
  {
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $ifIndex));

    foreach ($port_neighbours as $entry_id => $cdp_entry)
    {
      list($cdp_entry['cdpCacheDeviceId']) = explode('(', $cdp_entry['cdpCacheDeviceId']); // Fix for Nexus CDP neighbors: <hostname>(serial number)

      $remote_device_id = FALSE;
      if (is_valid_hostname($cdp_entry['cdpCacheDeviceId']))
      {
        if (isset($GLOBALS['cache']['discovery-protocols'][$cdp_entry['cdpCacheDeviceId']]))
        {
          // This hostname already checked, skip discover
          $remote_device_id = $GLOBALS['cache']['discovery-protocols'][$cdp_entry['cdpCacheDeviceId']];
        } else {
          $remote_device_id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($cdp_entry['cdpCacheDeviceId'], $cdp_entry['cdpCacheDeviceId']));

          // FIXME do LLDP-code-style hostname overwrite here as well? (see below)
          if (!$remote_device_id && is_valid_hostname($cdp_entry['cdpCacheDeviceId']) && !is_bad_xdp($cdp_entry['cdpCacheDeviceId'], $cdp_entry['cdpCachePlatform']))
          {
            $remote_device_id = discover_new_device($cdp_entry['cdpCacheDeviceId'], 'xdp', 'CDP', $device, $port);
          }

          // Cache remote device ID for other protocols
          $GLOBALS['cache']['discovery-protocols'][$cdp_entry['cdpCacheDeviceId']] = $remote_device_id;
        }

        if ($remote_device_id)
        {
          $if = $cdp_entry['cdpCacheDevicePort'];
          $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr` = ? OR `ifName` = ?) AND `device_id` = ?", array($if, $if, $remote_device_id));
        } else {
          $remote_port_id = "0";
        }

        if (!is_bad_xdp($cdp_entry['cdpCacheDeviceId']) && $port['port_id'] && $cdp_entry['cdpCacheDeviceId'] && $cdp_entry['cdpCacheDevicePort'])
        {
          $remote_address = hex2ip($cdp_entry['cdpCacheAddress']);
          if (!get_ip_version($remote_address)) { $remote_address = NULL; }

          discover_link($valid_link, $port['port_id'], 'cdp', $remote_port_id, $cdp_entry['cdpCacheDeviceId'], $cdp_entry['cdpCacheDevicePort'], $cdp_entry['cdpCachePlatform'], $cdp_entry['cdpCacheVersion'], $remote_address);
        }
      } else {
        echo("X");
      }
    }
  }
}

// EOF
