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

echo("JUNIPER-MIB ");

$jnxBoxDescr = snmp_get($device, 'jnxBoxDescr.0', '-OQv', 'JUNIPER-MIB', mib_dirs('juniper'));

if ($jnxBoxDescr)
{
  $jnxBoxSerialNo = snmp_get($device, 'jnxBoxSerialNo.0', '-OQv', 'JUNIPER-MIB', mib_dirs('juniper'));

  // Insert chassis as index 1, everything hangs off of this.
  $system_index = 1;
  $inventory[$system_index] = array(
    'entPhysicalDescr'        => $jnxBoxDescr,
    'entPhysicalClass'        => 'chassis',
    'entPhysicalName'         => 'Chassis',
    'entPhysicalSerialNum'    => $jnxBoxSerialNo,
    'entPhysicalIsFRU'        => 'true',
    'entPhysicalContainedIn'  => 0,
    'entPhysicalParentRelPos' => -1,
    'entPhysicalMfgName'      => 'Juniper'
  );

  discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], 'juniper-mib');

  // Now fetch data for the rest of the hardware in the chassis
  $data = snmpwalk_cache_oid($device, 'jnxContentsTable', array(), 'JUNIPER-MIB');
  $data = snmpwalk_cache_oid($device, 'jnxFruTable',        $data, 'JUNIPER-MIB');

  $global_relPos = 0;

  foreach ($data as $part)
  {
    // Index can only be int in the database, so we create our own from 7.1.1.0:
    $system_index = $part['jnxContentsContainerIndex'] * 16777216 + $part['jnxContentsL1Index'] * 65536 + $part['jnxContentsL2Index'] * 256 + $part['jnxContentsL3Index'];

    if ($system_index != 0)
    {
      if ($part['jnxContentsL2Index'] == 0 && $part['jnxContentsL3Index'] == 0)
      {
        $containedIn = 1; // Attach to chassis inserted above

        $global_relPos++; $relPos = $global_relPos;
      } else {
        $containerIndex = $part['jnxContentsContainerIndex'];

        if ($containerIndex == 8) { $containerIndex--; } // Convert PIC (8) to FPC (7) parent

        $containedIn = $containerIndex * 16777216 + $part['jnxContentsL1Index'] * 65536;

        $relPos = $part['jnxContentsL2Index'];
      }

      // [jnxFruTemp] => 45 - Could link to sensor somehow? (like we do for ENTITY-SENSOR-MIB)

      $inventory[$system_index] = array(
        'entPhysicalDescr'        => ucfirst($part['jnxContentsDescr']),
        'entPhysicalHardwareRev'  => $part['jnxContentsRevision'],
        'entPhysicalClass'        => (isset($part['jnxFruType']) ? $part['jnxFruType'] : 'chassis'),
        'entPhysicalName'         => ucfirst(($part['jnxFruName'] ? $part['jnxFruName'] : $part['jnxContentsDescr'])),
        'entPhysicalSerialNum'    => ($part['jnxContentsSerialNo'] == "BUILTIN" ? '' : str_replace('S/N ','',$part['jnxContentsSerialNo'])),
#        'entPhysicalModelName'    => $part['jnxContentsPartNo'],
        'entPhysicalIsFRU'        => (isset($part['jnxFruType']) ? 'true' : 'false'),
        'entPhysicalContainedIn'  => $containedIn,
        'entPhysicalParentRelPos' => $relPos,
        'entPhysicalMfgName'      => 'Juniper'
      );

      discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], 'juniper-mib');
    }
  }
}

// EOF
