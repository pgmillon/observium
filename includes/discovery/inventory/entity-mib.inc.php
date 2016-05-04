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

echo("ENTITY-MIB ");

$mibs = 'ENTITY-MIB';
$mib_dirs = mib_dirs();
foreach (array('ACMEPACKET', 'H3C', 'HH3C', 'CISCO') as $vendor_mib)
{
  $vendor_mib .= '-ENTITY-VENDORTYPE-OID-MIB';
  if (is_device_mib($device, $vendor_mib, FALSE))
  {
    $mibs .= ':'.$vendor_mib;
    $mib_dirs = mib_dirs($config['mibs'][$vendor_mib]['mib_dir']);
    break;
  }
}
$entity_array = snmpwalk_cache_oid($device, "entPhysicalEntry", array(), $mibs, $mib_dirs);
if ($GLOBALS['snmp_status'])
{
  $entity_array = snmpwalk_cache_twopart_oid($device, "entAliasMappingIdentifier", $entity_array, "ENTITY-MIB:IF-MIB", mib_dirs());

  $GLOBALS['cache']['entity-mib'] = $entity_array; // Cache this array for sensors discovery (see in cisco-entity-sensor-mib or entity-sensor-mib)

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

    if (isset($config['rewrites']['entPhysicalVendorTypes'][$entry['entPhysicalVendorType']]) && !$entry['entPhysicalModelName'])
    {
      $entry['entPhysicalModelName'] = $config['rewrites']['entPhysicalVendorTypes'][$entry['entPhysicalVendorType']];
    }

    if ($entry['entPhysicalDescr'] || $entry['entPhysicalName'])
    {
      discover_inventory($valid['inventory'], $device, $entPhysicalIndex, $entry);
    }
  }
}

// EOF
