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

//RAPID-CITY::rcSysDramSize.0 = INTEGER: 256
//RAPID-CITY::rcSysDramUsed.0 = Gauge32: 29
//RAPID-CITY::rcSysDramFree.0 = INTEGER: 184274
//RAPID-CITY::rcSysDramMaxBlockFree.0 = INTEGER: 122820

echo("RAPID-CITY ");

$mempool_array = snmp_get_multi($device, "rcSysDramSize.0 rcSysDramFree.0", "-OQUs", "RAPID-CITY", mib_dirs('nortel'));

if (is_numeric($mempool_array[0]['rcSysDramSize']))
{
  $total = $mempool_array[0]['rcSysDramSize'] * 1024;   // In MB
  $used  = $total - $mempool_array[0]['rcSysDramFree']; // In KB
  discover_mempool($valid['mempool'], $device, 0, "RAPID-CITY", "System Memory", 1024, $total, $used);
}
unset($mempool_array, $total, $used);

// EOF
