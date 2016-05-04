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

// GBNPlatformOAM-MIB::cpuDescription.0 = STRING: MIPS, 300MHz
// GBNPlatformOAM-MIB::cpuIdle.0 = INTEGER: 84

$mib = "gbnPlatformOAM-MIB";
echo("$mib ");

// Processor Utilization
$oid = ".1.3.6.1.4.1.13464.1.2.1.1.2.11.0";

$idle = snmp_get($device, $oid, "-OvQ", $mib, mib_dirs('gcom'));
$descr = snmp_get($device, ".1.3.6.1.4.1.13464.1.2.1.1.2.5.0", "-OvQ", $mib, mib_dirs('gcom'));
$usage = 100 - $idle;

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, 0, "gbnplatformoam-mib_cpuidle", $descr, 1, $usage, NULL, NULL, 1);
}

// EOF
