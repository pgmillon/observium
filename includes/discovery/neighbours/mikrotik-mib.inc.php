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

// mtxrNeighborIpAddress.1 = IpAddress: 192.168.4.27
// mtxrNeighborMacAddress.1 = STRING: 0:23:ac:53:3:28
// mtxrNeighborVersion.1 = STRING: Cisco IOS Software, C2960 Software (C2960-LANBASEK9-M), Version 15.0(1)SE2, RELEASE SOFTWARE (fc3)
// Technical Support: http://www.cisco.com/techsupport
// Copyright (c) 1986-2011 by Cisco Systems, Inc.
// Compiled Thu 22-Dec-11 00:46 by prod_rel_team
// mtxrNeighborPlatform.1 = STRING: cisco WS-C2960G-48TC-L
// mtxrNeighborIdentity.1 = STRING: switch.example.com
// mtxrNeighborSoftwareID.1 = STRING:
// mtxrNeighborInterfaceID.1 = INTEGER: 2

echo(" MIKROTIK-MIB ");

$mtxr_array = snmpwalk_cache_oid($device, "mtxrNeighbor", array(), "MIKROTIK-MIB", NULL, OBS_SNMP_ALL | OBS_SNMP_CONCAT);

if ($mtxr_array)
{
  if (OBS_DEBUG > 1) { print_vars($mtxr_array); }

  foreach ($mtxr_array as $key => $entry)
  {
    // Need to straighten out the MAC first for use later. Mikrotik does not pad the numbers! (i.e. 0:12:23:3:5c:6b)
    // FIXME move this to a smarter function?
    list($a,$b,$c,$d,$e,$f) = explode(':', $entry['mtxrNeighborMacAddress'],6);
    $entry['mtxrNeighborMacAddress'] = zeropad($a) . ':' . zeropad($b) . ':' . zeropad($c) . ':' . zeropad($d) . ':' . zeropad($e) . ':' . zeropad($f);

    $ifIndex = $entry['mtxrNeighborInterfaceID'];
  
    // Get the port using BRIDGE-MIB
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ? AND `ifDescr` NOT LIKE 'Vlan%'", array($device['device_id'], $ifIndex));

    $remote_device_id = FALSE;
    $remote_port_id   = 0;

    if (is_valid_hostname($entry['mtxrNeighborIdentity']))
    {
      if (isset($GLOBALS['cache']['discovery-protocols'][$entry['mtxrNeighborIdentity']]))
      {
        // This hostname already checked, skip discover
        $remote_device_id = $GLOBALS['cache']['discovery-protocols'][$entry['mtxrNeighborIdentity']];
      } else {
        $remote_device = dbFetchRow("SELECT `device_id`, `hostname` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($entry['mtxrNeighborIdentity'], $entry['mtxrNeighborIdentity']));
        $remote_device_id = $remote_device['device_id'];

        // If we don't know this device, try to discover it, as long as it's not matching our exclusion filters
        if (!$remote_device_id && !is_bad_xdp($entry['mtxrNeighborIdentity'], $entry['mtxrNeighborPlatform']))
        {
          $remote_device_id = discover_new_device($entry['mtxrNeighborIdentity'], 'xdp', 'MNDP', $device, $port);
        }

        // Cache remote device ID for other protocols
        $GLOBALS['cache']['discovery-protocols'][$entry['mtxrNeighborIdentity']] = $remote_device_id;
      }
    } else {
      // Try to find remote host by remote chassis mac address from DB
      $remote_mac = str_replace(':', '', strtolower($entry['mtxrNeighborMacAddress']));
      $remote_device_id = dbFetchCell("SELECT `device_id` FROM `ports` WHERE `deleted` = '0' AND `ifPhysAddress` = ? LIMIT 1;", array($remote_mac));
      if (!$remote_device_id)
      {
        // We can also use IP address from mtxrNeighborIpAddress to find remote device.
        $remote_device_id = dbFetchCell("SELECT `device_id` FROM `ports` LEFT JOIN `ipv4_addresses` on `ports`.`port_id`=`ipv4_addresses`.`port_id` WHERE `deleted` = '0' AND `ipv4_address` = ? LIMIT 1;", array($entry['mtxrNeighborIpAddress']));
      }
    }

    if ($remote_device_id)
    {
      $remote_device_hostname = device_by_id_cache($remote_device_id);

      // Overwrite remote hostname with the one we know, for devices that we identify by sysName
      if ($remote_device_hostname['hostname']) { $entry['mtxrNeighborIdentity'] = $remote_device_hostname['hostname']; }
    }

    if ($remote_device_id)
    {
      // No way to find a remote port other than by MAC address, with the data we're getting from Mikrotik. Only proceed when only one remote port matches...
      $remote_chassis_id = strtolower(str_replace(':','',$entry['mtxrNeighborMacAddress']));
      $remote_port_ids = dbFetchRows("SELECT `port_id` FROM `ports` WHERE `ifPhysAddress` = ? AND `device_id` = ?", array($remote_chassis_id, $remote_device_id));
      if (count($remote_port_ids) == 1) { $remote_port_id = $remote_port_ids[0]['port_id']; }
    }
    
    if (!is_bad_xdp($entry['mtxrNeighborIdentity']) && is_numeric($port['port_id']) && !empty($entry['mtxrNeighborIdentity']))
    {
      // We format the remote MAC just like lldpRemPortId macAddress (I think) (00 11 22 33 44 55) - we don't have an actual remote port name or ifIndex or anything in this MIB.
      discover_link($valid_link, $port['port_id'], 'mndp', $remote_port_id, $entry['mtxrNeighborIdentity'], strtoupper(str_replace(':', ' ',$entry['mtxrNeighborMacAddress'])), $entry['mtxrNeighborPlatform'], $entry['mtxrNeighborVersion']);
    }
  }
}

// EOF
