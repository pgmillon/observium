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

// HH3C-ENTITY-EXT-MIB::hh3cEntityExtCpuUsage.30 = INTEGER: 16
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtCpuUsage.36 = INTEGER: 5
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtCpuUsage.42 = INTEGER: 5
// HH3C-ENTITY-EXT-MIB::hh3cEntityExtCpuUsage.48 = INTEGER: 12

echo("HH3C-ENTITY-EXT-MIB ");

$chassis_count = 0;

$oids = array('hh3cEntityExtCpuUsage', 'entPhysicalName');
$processors_array = array();
foreach ($oids as $oid)
{
  $processors_array = snmpwalk_cache_multi_oid($device, $oid, $processors_array, 'ENTITY-MIB:HH3C-ENTITY-EXT-MIB', mib_dirs('hh3c'));
}

foreach ($processors_array as $index => $entry)
{
  if ($entry['hh3cEntityExtCpuUsage'] != 0)
  {
    $oid   = ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$index";
    $descr = $entry['entPhysicalName'];
    $usage = $entry['hh3cEntityExtCpuUsage'];
    discover_processor($valid['processor'], $device, $oid, $index, "hh3c-fixed", $descr, 1, $usage, NULL, NULL);
  }
}

unset ($processors_array);

// EOF
