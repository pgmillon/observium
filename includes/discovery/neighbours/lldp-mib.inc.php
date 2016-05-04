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

echo(" LLDP-MIB ");

$lldp_array = snmpwalk_cache_threepart_oid($device, "lldpRemoteSystemsData", array(), "LLDP-MIB", mib_dirs(), OBS_SNMP_ALL | OBS_SNMP_CONCAT);

if ($lldp_array)
{
  if (OBS_DEBUG > 1) { print_vars($lldp_array); }
  $dot1d_array = snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", array(), "BRIDGE-MIB", mib_dirs());
  $lldp_local_array = snmpwalk_cache_oid($device, "lldpLocalSystemData", array(), "LLDP-MIB", mib_dirs());

  foreach ($lldp_array as $key => $lldp_if_array)
  {
    foreach ($lldp_if_array as $entry_key => $lldp_instance)
    {
      if (is_numeric($dot1d_array[$entry_key]['dot1dBasePortIfIndex']) && $device['os'] != "junos") // FIXME why the junos exclude?
      {
        $ifIndex = $dot1d_array[$entry_key]['dot1dBasePortIfIndex'];
      } else {
        $ifIndex = $entry_key;
      }

      // Get the port using BRIDGE-MIB
      $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ? AND `ifDescr` NOT LIKE 'Vlan%'", array($device['device_id'], $ifIndex));

      // If BRIDGE-MIB failed, get the port using pure LLDP-MIB
      if (!$port)
      {
        $ifName = $lldp_local_array[$entry_key]['lldpLocPortDesc'];
        $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifDescr` = ?", array($device['device_id'], $ifName));
      }

      foreach ($lldp_instance as $entry_instance => $lldp)
      {
        $remote_device_id = FALSE;
        $remote_port_id   = 0;

        if (is_valid_hostname($lldp['lldpRemSysName']))
        {
          if (isset($GLOBALS['cache']['discovery-protocols'][$lldp['lldpRemSysName']]))
          {
            // This hostname already checked, skip discover
            $remote_device_id = $GLOBALS['cache']['discovery-protocols'][$lldp['lldpRemSysName']];
          } else {
            $remote_device = dbFetchRow("SELECT `device_id`, `hostname` FROM `devices` WHERE `sysName` = ? OR `hostname` = ?", array($lldp['lldpRemSysName'], $lldp['lldpRemSysName']));
            $remote_device_id = $remote_device['device_id'];

            // Overwrite remote hostname with the one we know, for devices that we identify by sysName
            if ($remote_device['hostname']) { $lldp['lldpRemSysName'] = $remote_device['hostname']; }

            // If we don't know this device, try to discover it, as long as it's not matching our exclusion filters        
            if (!$remote_device_id && !is_bad_xdp($lldp['lldpRemSysName'], $lldp['lldpRemSysDesc'])) // NOTE, LLDP not have any usable Platform name, here we use lldpRemSysDesc
            {
              $remote_device_id = discover_new_device($lldp['lldpRemSysName'], 'xdp', 'LLDP', $device, $port);
            }

            // Cache remote device ID for other protocols
            $GLOBALS['cache']['discovery-protocols'][$lldp['lldpRemSysName']] = $remote_device_id;
          }
        } else {
          // Try to find remote host by remote chassis mac address from DB
          if (empty($lldp['lldpRemSysName']) && $lldp['lldpRemChassisIdSubtype'] == 'macAddress')
          {
            $remote_mac = str_replace(array(' ', '-', ':'), '', strtolower($lldp['lldpRemChassisId']));
            $remote_device_id = dbFetchCell("SELECT `device_id` FROM `ports` WHERE `deleted` = '0' AND `ifPhysAddress` = ? LIMIT 1;", array($remote_mac));
            if ($remote_device_id)
            {
              $remote_device_hostname = device_by_id_cache($remote_device_id);
              if ($remote_device_hostname['hostname'])
              {
                $lldp['lldpRemSysName'] = $remote_device_hostname['hostname'];
              }
            }
          }
        }

        if ($remote_device_id)
        {
          $if = $lldp['lldpRemPortDesc']; $id = $lldp['lldpRemPortId'];

          // lldpPortIdSubtype   -> lldpPortId
          //  interfaceAlias(1), ->  ifAlias
          //  portComponent(2),  ->  entPhysicalAlias
          //  macAddress(3),     ->  ifPhysAddress
          //  networkAddress(4), ->  IP address
          //  interfaceName(5),  ->  ifName
          //  agentCircuitId(6), ->  agent-local identifier of the circuit (defined in RFC 3046) (FIXME, not know)
          //  local(7)           ->  ifIndex
          switch ($lldp['lldpRemPortIdSubtype'])
          {
            case 'interfaceAlias':
              $id = snmp_hexstring($id);
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifAlias` = ? OR `ifDescr` = ?) AND `device_id` = ?", array($id, $if, $remote_device_id));
              break;
            case 'interfaceName':
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifName` = ?  OR `ifDescr` = ?) AND `device_id` = ?", array($id, $if, $remote_device_id));
              break;
            case 'macAddress':
              $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `ifPhysAddress` = ? AND `device_id` = ?", array(strtolower(str_replace(array(' ', '-'), '', $id)), $remote_device_id));
              break;
            case 'networkAddress':
              $ip_version = get_ip_version($id);
              if ($ip_version)
              {
                $ip = ($ip_version === 6 ? Net_IPv6::uncompress($id, TRUE) : $id);
                $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ipv".$ip_version."_addresses` LEFT JOIN `ports` USING (`port_id`) WHERE `ipv".$ip_version."_address` = ? AND `device_id` = ?", array($ip, $remote_device_id));
              }
              break;
            case 'ifIndex':
            case 'local':
              // These cases are handled by the ifDescr/ifIndex combination fallback below
            default:
              break;
          }

          if (!$remote_port_id) // Not found despite our attempts above - fall back to try matching with ifDescr/ifIndex
          {
            $remote_port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE (`ifIndex`= ? OR `ifDescr` = ?) AND `device_id` = ?", array($id, $if, $remote_device_id));
          }

          if (!$remote_port_id) // Still not found?
          {
            if ($lldp['lldpRemChassisIdSubtype'] == 'macAddress')
            {
              // Find the port by chassis MAC address, only use this if exactly 1 match is returned, otherwise we'd link wrongly - think switches with 1 global MAC on all ports.
              $remote_chassis_id = strtolower(str_replace(array(' ', '-'),'',$lldp['lldpRemChassisId']));
              $remote_port_ids = dbFetchRows("SELECT `port_id` FROM `ports` WHERE `ifPhysAddress` = ? AND `device_id` = ?", array($remote_chassis_id, $remote_device_id));
              if (count($remote_port_ids) == 1) { $remote_port_id = $remote_port_ids[0]['port_id']; }
            }
          }
        } else {
          // FIXME why is this here?
          if (empty($lldp['lldpRemSysName']) && $lldp['lldpRemChassisIdSubtype'] == 'macAddress')
          {
            $lldp['lldpRemSysName'] = str_replace(array(' ', '-'), '', strtolower($lldp['lldpRemChassisId']));
          }
        }

        if (!is_bad_xdp($lldp['lldpRemSysName']) && is_numeric($port['port_id']) && !empty($lldp['lldpRemSysName']) && isset($lldp['lldpRemPortId']))
        {
          // FIXME. We can use lldpRemSysCapEnabled as platform, but they use BITS textual conversion:
          // LLDP-MIB::lldpRemSysCapEnabled.0.5.3 = BITS: 20 00 bridge(2)
          // LLDP-MIB::lldpRemSysCapEnabled.0.5.3 = "20 00 "
          discover_link($valid_link, $port['port_id'], 'lldp', $remote_port_id, $lldp['lldpRemSysName'], $lldp['lldpRemPortId'], NULL, $lldp['lldpRemSysDesc']);
        }
      }
    }
  }
}

// EOF
