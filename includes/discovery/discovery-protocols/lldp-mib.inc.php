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

echo(" LLDP-MIB ");

$lldp_array = snmpwalk_cache_threepart_oid($device, "lldpRemoteSystemsData", array(), "LLDP-MIB", mib_dirs());

if ($lldp_array)
{
  $dot1d_array = snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", array(), "BRIDGE-MIB", mib_dirs());

  foreach (array_keys($lldp_array) as $key)
  {
    $lldp_if_array = $lldp_array[$key];
    foreach (array_keys($lldp_if_array) as $entry_key)
    {
      if (is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex']) && $device['os'] != "junos") // FIXME why the junos exclude?
      {
        $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
      } else {
        $ifIndex = $entry_key;
      }

      $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $ifIndex));
      $lldp_instance = $lldp_if_array[$entry_key];
      foreach (array_keys($lldp_instance) as $entry_instance)
      {
        $lldp = $lldp_instance[$entry_instance];
        $remote_device_id = FALSE;
        if (is_valid_hostname($lldp['lldpRemSysName']))
        {
          $remote_device = dbFetchRow("SELECT `device_id`, `hostname` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($lldp['lldpRemSysName'], $lldp['lldpRemSysName']));
          $remote_device_id = $remote_device['device_id'];

          // Overwrite remote hostname with the one we know, for devices that we identify by sysName
          if ($remote_device['hostname']) { $lldp['lldpRemSysName'] = $remote_device['hostname']; }
        }

        if (!$remote_device_id && is_valid_hostname($lldp['lldpRemSysName']) && !is_bad_xdp($lldp['lldpRemSysName']))
        {
          $remote_device_id = discover_new_device($lldp['lldpRemSysName'], 'xdp', 'LLDP', $device, $port);
        }

        if ($remote_device_id)
        {
          $if = $lldp['lldpRemPortDesc']; $id = $lldp['lldpRemPortId'];

          switch ($lldp['lldpRemPortIdSubtype'])
          {
            case 'interfaceName':
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifDescr`= ? OR `ifName`= ?) AND `device_id` = ?",array($if, $id, $remote_device_id));
              break;
            case 'macAddress':
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `ifPhysAddress` = ? AND `device_id` = ?", array(str_replace(' ', '', $id), $remote_device_id));
              break;
            case 'ifIndex':
            default:
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?",array($id, $remote_device_id));
              break;
          }

          if (!$remote_port_id) // Still not found?
          {
            if ($lldp['lldpRemChassisIdSubtype'] == 'macAddress')
            { // Find the device by MAC address, still matches multiple ports sometimes, we use the first one and hope we're lucky
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `ifPhysAddress` = ? AND `device_id` = ?", array(str_replace(' ','',$lldp['lldpRemChassisId']),$remote_device_id));
            }
          }
        } else {
          if (empty($lldp['lldpRemSysName']) && $lldp['lldpRemChassisIdSubtype'] == 'macAddress')
          {
            $lldp['lldpRemSysName'] = str_replace(' ', '', strtolower($lldp['lldpRemChassisId']));
          }
          $remote_port_id = "0";
        }

        if (!is_bad_xdp($lldp['lldpRemSysName']) && is_numeric($port['port_id']) && !empty($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId']))
        {
          discover_link($valid_link, $port['port_id'], 'lldp', $remote_port_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], NULL, $lldp['lldpRemSysDesc']);
        }
      }
    }
  }
}

// EOF
