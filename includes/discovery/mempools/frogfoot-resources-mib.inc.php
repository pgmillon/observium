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

# FROGFOOT-RESOURCES-MIB::memTotal.0 = Gauge32: 29524
# FROGFOOT-RESOURCES-MIB::memFree.0 = Gauge32: 4584
# FROGFOOT-RESOURCES-MIB::memBuffer.0 = Gauge32: 3584

$mib = 'FROGFOOT-RESOURCES-MIB';
echo(" $mib ");

$free  = snmp_get($device, "memFree.0", "-OvQ", $mib, mib_dirs('ubiquiti'));
$total = snmp_get($device, "memTotal.0", "-OvQ", $mib, mib_dirs('ubiquiti'));
$used = $total - $free;

if (is_numeric($total) && is_numeric($used))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1024, $total * 1024, $used * 1024);
}
unset ($total, $used, $free);

// EOF
