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

echo("E7-Calix-MIB ");

// This device not have self Indexes.
// Use workaround ($base_vendor_index * 100000) + ($e7CardBank * 1000) + $e7CardIndex
$base_vendor_index = 6321;
$inventory_mib = 'e7-calix';

// System
$e7SystemId = snmp_get($device, 'e7SystemId.0', '-OQUs', 'E7-Calix-MIB', mib_dirs('calix'));
if ($e7SystemId)
{
  $e7SystemChassisSerialNumber = snmp_get($device, '.1.3.6.1.4.1.6321.1.2.2.2.1.7.10.0', '-Oqvn');
  $system_index = $base_vendor_index * 100000;
  $inventory[$system_index] = array(
    'entPhysicalDescr'        => 'Calix Networks, E7 Ethernet Service Access Platform',
    'entPhysicalClass'        => 'chassis',
    'entPhysicalName'         => 'E7 ESAP',
    'entPhysicalSerialNum'    => $e7SystemChassisSerialNumber,
    'entPhysicalAssetID'      => $e7SystemId,
    'entPhysicalIsFRU'        => 'true',
    'entPhysicalContainedIn'  => 0,
    'entPhysicalParentRelPos' => 0,
    'entPhysicalMfgName'      => 'Calix'
  );
  discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], $inventory_mib);

  // Cards
  $E7CardEntry = snmpwalk_cache_twopart_oid($device, 'E7CardEntry', array(), 'E7-Calix-MIB', mib_dirs('calix'));
  foreach ($E7CardEntry as $e7CardBank => $entries)
  {
    $bank_index = $system_index + $e7CardBank * 1000;
    $inventory[$bank_index] = array(
      'entPhysicalDescr'        => 'E7 ESAP Bank',
      'entPhysicalClass'        => 'container',
      'entPhysicalName'         => 'Bank '.$e7CardBank,
      'entPhysicalIsFRU'        => 'false',
      'entPhysicalContainedIn'  => $system_index,
      'entPhysicalParentRelPos' => $e7CardBank,
      'entPhysicalMfgName'      => 'Calix'
    );
    discover_inventory($valid['inventory'], $device, $bank_index, $inventory[$bank_index], $inventory_mib);

    foreach ($entries as $e7CardIndex => $entry)
    {
      $card_index = $bank_index + $e7CardIndex;
      $inventory[$card_index] = array(
        'entPhysicalDescr'        => 'E7 ESAP Card',
        'entPhysicalClass'        => 'other',
        'entPhysicalName'         => 'Card '.ucfirst($entry['e7CardActualType']),
        'entPhysicalVendorType'   => $entry['e7CardActualType'],
        'entPhysicalSerialNum'    => $entry['e7CardSerialNumber'],
        'entPhysicalSoftwareRev'  => $entry['e7CardSoftwareVersion'],
        'entPhysicalIsFRU'        => 'false',
        'entPhysicalContainedIn'  => $bank_index,
        'entPhysicalParentRelPos' => $e7CardIndex,
        'entPhysicalMfgName'      => 'Calix'
      );
      discover_inventory($valid['inventory'], $device, $card_index, $inventory[$card_index], $inventory_mib);
    }
  }

  if (OBS_DEBUG > 1 && count($inventory)) { print_vars($inventory); }
}

// EOF
