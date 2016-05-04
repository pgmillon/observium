<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

#ALCATEL-IND1-SYSTEM-MIB::systemHardwareMemoryMfg.0 = INTEGER: notreadable(12)
#ALCATEL-IND1-SYSTEM-MIB::systemHardwareMemorySize.0 = Gauge32: 268435456
#ALCATEL-IND1-HEALTH-MIB::healthDeviceMemoryLatest.0 = INTEGER: 74
#ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1MinAvg.0 = INTEGER: 74
#ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1HrAvg.0 = INTEGER: 74
#ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1HrMax.0 = INTEGER: 74

// NOTE. Because Alcatel changed their MIBs content (same oid names have different indexes), here used only numeric OIDs.

$mib = 'ALCATEL-IND1-HEALTH-MIB';

if (!$mempool['mempool_hc'])
{
  // Old AOS
  $mempool['total'] = snmp_get($device, ".1.3.6.1.4.1.6486.800.1.1.1.2.1.1.3.4.0", "-OvQ", "ALCATEL-IND1-SYSTEM-MIB", mib_dirs('aos'));
  $mempool['perc']  = snmp_get($device, ".1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.10.0", "-OvQ", $mib, mib_dirs('aos'));
} else {
  // New AOS
  $mempool['total'] = snmp_get($device, ".1.3.6.1.4.1.6486.801.1.1.1.2.1.1.3.4.0", "-OvQ", "ALCATEL-IND1-SYSTEM-MIB", mib_dirs('aos7'));
  $mempool['total'] *= 1024; // AOS7 reports total memory in MB
  $mempool['perc']  = snmp_get($device, ".1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.8.0", "-OvQ", $mib, mib_dirs('aos7'));
}

// EOF
