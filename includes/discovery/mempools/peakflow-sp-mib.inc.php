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

//PEAKFLOW-SP-MIB::devicePhysicalMemory.0 = INTEGER: 8293156
//PEAKFLOW-SP-MIB::devicePhysicalMemoryInUse.0 = INTEGER: 2493368
//PEAKFLOW-SP-MIB::devicePhysicalMemoryUsage.0 = INTEGER: 30

if (!is_device_mib($device, 'HOST-RESOURCES-MIB')) // Memory pools already avialable in HOST-RESOURCES-MIB
{
  echo("PEAKFLOW-SP-MIB ");

  $mempool_array = snmp_get_multi($device, "devicePhysicalMemory.0 devicePhysicalMemoryInUse.0", "-OQUs", "PEAKFLOW-SP-MIB");

  discover_mempool($valid['mempool'], $device, 0, "PEAKFLOW-SP-MIB", "Physical Memory", 1024, $mempool_array[0]['devicePhysicalMemory'], $mempool_array[0]['devicePhysicalMemoryInUse']);

  unset ($mempool_array);
}

// EOF
