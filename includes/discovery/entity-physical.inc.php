<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Physical Inventory : ");

$entity_array = snmpwalk_cache_oid($device, "entPhysicalEntry", array(), "ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs('cisco'));
$entity_array = snmpwalk_cache_twopart_oid($device, "entAliasMappingIdentifier", $entity_array, "ENTITY-MIB:IF-MIB");

foreach ($entity_array as $entPhysicalIndex => $entry)
{
  if (isset($entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier']))
  {
    $ifIndex = $entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'];
    if (!strpos($ifIndex, "fIndex") || $ifIndex == '')
    {
      unset($ifIndex);
    } else {
      list(,$ifIndex) = explode(".", $ifIndex);
      $entry['ifIndex'] = $ifIndex;
    }
  }

  if (isset($entPhysicalVendorTypes[$entry['entPhysicalVendorType']]) && !$entry['entPhysicalModelName'])
  {
    $entry['entPhysicalModelName'] = $entPhysicalVendorTypes[$entry['entPhysicalVendorType']];
  }

  if ($entry['entPhysicalDescr'] || $entry['entPhysicalName'])
  {
    discover_inventory($valid['inventory'], $device, $entPhysicalIndex, $entry);
  }
}

echo("\n");

// EOF
