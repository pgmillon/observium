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

// HH3C-ENTITY-EXT-MIB::hh3cEntityExtMemUsage.30 = INTEGER: 58
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtMemUsage.36 = INTEGER: 59
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtMemUsage.42 = INTEGER: 58
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtMemUsage.48 = INTEGER: 58

$mib = 'HH3C-ENTITY-EXT-MIB';
echo(" $mib ");

$oids = array('hh3cEntityExtMemUsage', 'entPhysicalName');
$mempool_array = array();
foreach ($oids as $oid)
{
  $mempool_array = snmpwalk_cache_multi_oid($device, $oid, $mempool_array, 'ENTITY-MIB:HH3C-ENTITY-EXT-MIB', mib_dirs('hh3c'));
}

if (is_array($mempool_array))
{
  $chassis_count = 0;
  $mempool_array = snmpwalk_cache_oid($device, "hh3cEntityExtMemSize", $mempool_array, $mib, mib_dirs('hh3c'));

  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['hh3cEntityExtMemUsage']) && $entry['hh3cEntityExtMemSize'] > 0)
    {
      $descr   = $entry['entPhysicalName'];
      $percent = $entry['hh3cEntityExtMemUsage'];
      $total   = $entry['hh3cEntityExtMemSize'];
      $used    = $total * $percent / 100;

      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, 1, $total, $used);
    }
  }
}

unset ($mempool_array, $index, $descr, $total, $used, $chassis_count, $percent);

// EOF
