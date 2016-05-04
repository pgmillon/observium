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

$mib = 'Dell-Vendor-MIB';
echo("$mib ");

# Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.1.0 = INTEGER: 23127
# Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.2.0 = INTEGER: 262144

// In fact it - FASTPATH-SWITCHING-MIB::agentSwitchCpuProcessMemFree, but Dell hide this MIB
$free   = snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.1.0", "-OvQ", "Dell-Vendor-MIB", mib_dirs('dell'));
$total  = snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.2.0", "-OvQ", "Dell-Vendor-MIB", mib_dirs('dell'));
$used   = $total - $free;
//$total *= 1024;
//$used  *= 1024;

if (is_numeric($free))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1024, $total, $used);
}
unset ($total, $used, $free);

// EOF
