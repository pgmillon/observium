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

// NOTE. Because Alcatel changed their MIBs content (same oid names have different indexes), here used only numeric OIDs.

$mib = 'ALCATEL-IND1-HEALTH-MIB';
echo("$mib ");

$total   = snmp_get($device, ".1.3.6.1.4.1.6486.800.1.1.1.2.1.1.3.4.0", "-OvQ", "ALCATEL-IND1-SYSTEM-MIB", mib_dirs('aos')); // systemHardwareMemorySize
$percent = snmp_get($device, ".1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.10.0", "-OvQ", $mib, mib_dirs('aos'));                    // healthModuleMemory1MinAvg
$used    = $total / 100 * $percent;

if (is_numeric($total) && is_numeric($percent))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, $total, $used);
}
unset ($total, $used, $percent);

// New AOS 7
$total   = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.1.1.2.1.1.3.4.0', "-OvQ", "ALCATEL-IND1-SYSTEM-MIB", mib_dirs('aos7')); // systemHardwareMemorySize
$percent = snmp_get($device, '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.0', "-OvQ", $mib, mib_dirs('aos7'));                 // healthModuleMemory1MinAvg
$used    = $total / 100 * $percent;

if (is_numeric($total) && is_numeric($percent))
{
  $total *= 1024; // AOS7 reports total memory in MB
  // Use HC bit for new aos
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, $total, $used, 1);
}
unset ($total, $used, $percent);

// EOF
