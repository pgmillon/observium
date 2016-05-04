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

// AT-SYSINFO-MIB::cpuUtilisationMax.0 = INTEGER: 43
// AT-SYSINFO-MIB::cpuUtilisationAvg.0 = INTEGER: 4
// AT-SYSINFO-MIB::cpuUtilisationAvgLastMinute.0 = INTEGER: 3
// AT-SYSINFO-MIB::cpuUtilisationAvgLast10Seconds.0 = INTEGER: 6
// AT-SYSINFO-MIB::cpuUtilisationAvgLastSecond.0 = INTEGER: 3
// AT-SYSINFO-MIB::cpuUtilisationMaxLast5Minutes.0 = INTEGER: 45
// AT-SYSINFO-MIB::cpuUtilisationAvgLast5Minutes.0 = INTEGER: 6
// AT-SYSINFO-MIB::atContactDetails.0 = STRING: Allied Telesis Inc. alliedtelesis.com
// AT-SYSINFO-MIB::freeMemory.0 = INTEGER: 83
// AT-SYSINFO-MIB::totalBuffers.0 = INTEGER: 13246464

//  Hardcoded discovery of cpu usage on Alliedware/Alliedwareplus devices using AT-SYSINFO-MIB

echo("AT-SYSINFO-MIB ");

$descr = "Processor";
$usage = snmp_get($device, "cpuUtilisationAvgLast5Minutes.0", "-OQUvs", "AT-SYSINFO-MIB");

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.207.8.4.4.3.3.7.0", 0, "at-sysinfo-mib", $descr, 1, $usage, NULL, NULL);
}

// EOF
