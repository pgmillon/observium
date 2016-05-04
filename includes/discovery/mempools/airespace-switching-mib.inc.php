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

$mib = 'AIRESPACE-SWITCHING-MIB';
echo("$mib ");

//AIRESPACE-SWITCHING-MIB::agentFreeMemory.0 = 466732
//AIRESPACE-SWITCHING-MIB::agentTotalMemory.0 = 1000952
$free  = snmp_get($device, 'agentFreeMemory.0',  '-OQUvs', $mib);
$total = snmp_get($device, 'agentTotalMemory.0', '-OQUvs', $mib);
$units = 1024;

if (is_numeric($free) && is_numeric($total))
{
  //$free  *= $units;
  //$total *= $units;
  $used   = $total - $free;
  discover_mempool($valid['mempool'], $device, 0, $mib, 'Memory', $units, $total, $used);
}

unset ($total, $used, $free, $units);

// EOF
