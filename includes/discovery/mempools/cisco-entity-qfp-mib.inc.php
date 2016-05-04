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

$mib = 'CISCO-ENTITY-QFP-MIB';
echo("$mib ");

$mempool_array = snmpwalk_cache_multi_oid($device, "ceqfpMemoryResourceEntry", NULL, $mib, mib_dirs('cisco'));

if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['ceqfpMemoryResInUse']))
    {
      list($entPhysicalIndex, $entMemoryResource) = explode(".", $index);
      $entPhysicalName = snmp_get($device, "entPhysicalName.".$entPhysicalIndex, "-Oqv", "ENTITY-MIB", mib_dirs());

      $descr = $entPhysicalName." - ".$entMemoryResource;
      $used  = $entry['ceqfpMemoryResInUse'];
      $total = $entry['ceqfpMemoryResTotal'];

      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $descr, $total, $used, $free, $entPhysicalIndex, $entPhysicalName, $entMemoryResource);

// EOF
