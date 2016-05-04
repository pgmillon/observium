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

//TIMETRA-SYSTEM-MIB::sgiMemoryUsed.0 = Gauge32: 300145144 bytes
//TIMETRA-SYSTEM-MIB::sgiMemoryAvailable.0 = Gauge32: 518611632 bytes
//TIMETRA-SYSTEM-MIB::sgiMemoryPoolAllocated.0 = Gauge32: 320917080 bytes

//TIMETRA-SYSTEM-MIB::sgiMemoryUsed.0 = Gauge32: 305605608 bytes
//TIMETRA-SYSTEM-MIB::sgiMemoryAvailable.0 = Gauge32: 600985024 bytes
//TIMETRA-SYSTEM-MIB::sgiMemoryPoolAllocated.0 = Gauge32: 325038952 bytes
/*
  If the value is greater than the maximum value reportable by this
  object then this object reports its maximum value (4,294,967,295)
  and sgiKbMemoryPoolAllocated must be used to determine the total
  memory allocated in memory-pools.

  FIXME: sgiKbMemoryUsed, sgiKbMemoryAvailable, sgiKbMemoryPoolAllocated (use HC bit)
*/

echo("TIMETRA-SYSTEM-MIB ");

$mempool_array = snmpwalk_cache_multi_oid($device, "sgiMemoryAvailable",      NULL, "TIMETRA-SYSTEM-MIB");
$mempool_array = snmpwalk_cache_multi_oid($device, "sgiMemoryUsed", $mempool_array, "TIMETRA-SYSTEM-MIB");

if (is_numeric($mempool_array[0]['sgiMemoryUsed']))
{
  discover_mempool($valid['mempool'], $device, 0, "TIMETRA-SYSTEM-MIB", "Memory", 1, $mempool_array[0]['sgiMemoryAvailable'], $mempool_array[0]['sgiMemoryUsed']);
}

unset ($mempool_array);

// EOF
