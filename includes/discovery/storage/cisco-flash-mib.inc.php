<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$mib = 'CISCO-FLASH-MIB';
echo(" $mib ");

// CISCO-FLASH-MIB::ciscoFlashDevicesSupported.0 = Gauge32: 7
$ciscoFlashDevicesSupported = snmp_get($device, "ciscoFlashDevicesSupported.0", "-Ovq", $mib, mib_dirs('cisco')); // Number of Flash devices supported by the system

if ((int)$ciscoFlashDevicesSupported > 0)
{
  $ciscoFlashDeviceTable = snmpwalk_cache_oid($device, 'ciscoFlashDeviceTable', NULL, $mib . ':OLD-CISCO-CHASSIS-MIB', mib_dirs('cisco'));
  //$ciscoFlashDeviceTable = snmpwalk_cache_oid($device, 'ciscoFlashDeviceName', NULL, $mib . ':OLD-CISCO-CHASSIS-MIB', mib_dirs('cisco'));

  if ($GLOBALS['snmp_status'])
  {
    // Disable retries
    $device_tmp = $device;
    //$device_tmp['snmp_retries'] = 1;
    //$device_tmp['snmp_nobulk'] = TRUE;

    $has_hc = FALSE;
    foreach ($ciscoFlashDeviceTable as $flash)
    {
      if (isset($flash['ciscoFlashDeviceSizeExtended']))
      {
        $has_hc = TRUE;
        break;
      }
    }

    // Fetch only required oids, do not use walk!
    //$oids = array('ciscoFlashDeviceDescr', 'ciscoFlashDeviceRemovable', 'ciscoFlashDevicePartitions', 'ciscoFlashDeviceSizeExtended');
    //foreach ($oids as $oid)
    //{
    //  $ciscoFlashDeviceTable = snmpwalk_cache_oid($device_tmp, $oid, $ciscoFlashDeviceTable, $mib . ':OLD-CISCO-CHASSIS-MIB', mib_dirs('cisco'));
    //  if ($oid == 'ciscoFlashDeviceSizeExtended')
    //  {
    //    $has_hc = $GLOBALS['snmp_status'];
    //  }
    //}
    if (OBS_DEBUG > 1 && count($ciscoFlashDeviceTable)) { print_vars($ciscoFlashDeviceTable); }

    sleep(5); // Yes, really.. sleep here, because cisco freeze

    //$ciscoFlashPartitionTable = snmpwalk_cache_twopart_oid($device_tmp, 'ciscoFlashPartitionTable', NULL, $mib, mib_dirs('cisco'));
    $ciscoFlashPartitionTable = snmpwalk_cache_twopart_oid($device_tmp, 'ciscoFlashPartitionName', NULL, $mib, mib_dirs('cisco'));
    /*
    if ($has_hc)
    {
      $oids = array('ciscoFlashPartitionSizeExtended', 'ciscoFlashPartitionFreeSpaceExtended');
    } else {
      $oids = array('ciscoFlashPartitionSize', 'ciscoFlashPartitionFreeSpace');
    }
    foreach ($oids as $oid)
    {
      sleep(3); // Yes, really.. sleep here, because cisco freeze
      $ciscoFlashPartitionTable = snmpwalk_cache_twopart_oid($device_tmp, $oid, $ciscoFlashPartitionTable, $mib, mib_dirs('cisco'));
    }
    */
    if (OBS_DEBUG > 1 && count($ciscoFlashPartitionTable)) { print_vars($ciscoFlashPartitionTable); }

    if ($GLOBALS['snmp_error_code'] == 1002)
    {
      // We get timeout error here, f* cisco with your shit ;)
      // Additional sleep here and completely disable this mib now, for do not use it next time...
      sleep(5);
      set_entity_attrib('device', $device, 'mib_' . $mib, "0"); /// FIXME. Note for myself, replace later with set_device_mibs_disabled(), not released yet
      log_event('Note, polling/discovery by MIB "' . $mib . '" disabled, due to produced many Timeout errors. You can enable it again in device "Properties -> MIBs" page.', $device, 'device', $device['device_id'], 'warning');
    }
    sleep(5); // Yes, really.. sleep here, because cisco freeze and next discovery module return empty
  }

  foreach ($ciscoFlashDeviceTable as $flash_index => $flash)
  {
    /*
    CISCO-FLASH-MIB::ciscoFlashDeviceSize.6 = Gauge32: 2048425984 bytes
    CISCO-FLASH-MIB::ciscoFlashDeviceSize.7 = Gauge32: 0 bytes
    CISCO-FLASH-MIB::ciscoFlashDevicePartitions.6 = Gauge32: 1
    CISCO-FLASH-MIB::ciscoFlashDevicePartitions.7 = Gauge32: 0
    CISCO-FLASH-MIB::ciscoFlashDeviceName.6 = STRING: disk0
    CISCO-FLASH-MIB::ciscoFlashDeviceName.7 = STRING: disk1
    CISCO-FLASH-MIB::ciscoFlashDeviceDescr.6 = STRING: Disk 0 Flash
    CISCO-FLASH-MIB::ciscoFlashDeviceDescr.7 = STRING: Disk 1 Flash
    CISCO-FLASH-MIB::ciscoFlashDeviceRemovable.6 = INTEGER: true(1)
    CISCO-FLASH-MIB::ciscoFlashDeviceRemovable.7 = INTEGER: true(1)
    */
    $fstype = ($flash['ciscoFlashDeviceRemovable'] == 'true' ? 'ciscoFlashRemovable' : 'ciscoFlash');
    if (isset($flash['ciscoFlashDeviceSizeExtended']) && $flash['ciscoFlashDeviceSizeExtended'] > 0)
    {
      $hc   = 1;
    } else {
      $hc   = 0;
    }

    // Do not skip removable for Cisco devices
    //if (isset($config['ignore_mount_removable']) && $config['ignore_mount_removable'] && $fstype == "ciscoFlashRemovable") { print_debug("Skipped removable: $descr"); continue; }
    if (!$flash['ciscoFlashDeviceSize']) { continue; } // Skip currently not exist flash disks

    foreach ($ciscoFlashPartitionTable[$flash_index] as $partition_index => $partition)
    {
      /*
      CISCO-FLASH-MIB::ciscoFlashPartitionSize.6.1 = Gauge32: 2048425984 bytes
      CISCO-FLASH-MIB::ciscoFlashPartitionFreeSpace.6.1 = Gauge32: 1380122624 bytes
      */
      $index = "$flash_index.$partition_index";
      $descr = ($flash['ciscoFlashDeviceDescr'] ? $flash['ciscoFlashDeviceDescr'] : $flash['ciscoFlashDeviceName']);
      // Clean some descriptions:
      // ciscoFlashDeviceDescr.2 = Cat4000 Private Flash Area (Not available for general use)
      list($descr) = explode(' (', $descr);
      if (($flash['ciscoFlashDevicePartitions'] > 1) || ($partition['ciscoFlashPartitionName'][0] === '/'))
      {
        $descr .= ' - ' . $partition['ciscoFlashPartitionName'];
      }
      /*
      if ($hc)
      {
        $size = $partition['ciscoFlashPartitionSizeExtended'];
        $free = $partition['ciscoFlashPartitionFreeSpaceExtended'];
      } else {
        $size = $partition['ciscoFlashPartitionSize'];
        $free = $partition['ciscoFlashPartitionFreeSpace'];
      }
      $used = $size - $free;
      */

      // FIXME. Skip based on ciscoFlashPartitionStatus: readOnly, runFromFlash, readWrite
      //if ($partition['ciscoFlashPartitionStatus'] != 'readWrite') { continue; }

      //discover_storage($valid['storage'], $device, $index, $fstype, $mib, $descr, 1, $size, $used, $hc);
      discover_storage($valid['storage'], $device, $index, $fstype, $mib, $descr, 1, 1, 0, $hc); // Fake size/used - updated later by poller
    }
  }
}

unset ($device_tmp, $flash, $flash_index, $partition, $partition_index, $index, $descr, $size, $used, $free, $hc);

//I know, that this too long discovery, but it better than nothing

// EOF
