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

// Hardcoded discovery of device CPU usage on Alcatel-Lucent Omniswitches.
// NOTE. Because Alcatel changed their MIBs content (same oid names have different indexes), here used only numeric OIDs.

echo("ALCATEL-IND1-HEALTH-MIB ");

// Old AOS
$descr = 'Device CPU';
$oid   = '.1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.14.0'; // healthModuleCpu1MinAvg
$usage = snmp_get($device, $oid, '-OQUvs', 'ALCATEL-IND1-HEALTH-MIB', mib_dirs('aos'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, '0', 'aos-system', $descr, "1", $usage, NULL, NULL);
}
unset($usage);

// New AOS 7
$oid   = '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.0'; // healthModuleCpu1MinAvg
$usage = snmp_get($device, $oid, '-OQUvs', 'ALCATEL-IND1-HEALTH-MIB', mib_dirs('aos7'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, '0', 'aos-system', $descr, "1", $usage, NULL, NULL);
}

// EOF
