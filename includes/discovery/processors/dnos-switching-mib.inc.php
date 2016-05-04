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

// DNOS-SWITCHING-MIB::agentSwitchCpuProcessTotalUtilization.0 = STRING: "    5 Secs (  6.510%)   60 Secs (  7.724%)  300 Secs (  6.3812%)"

echo("DNOS-SWITCHING-MIB ");

$oid   = 'agentSwitchCpuProcessTotalUtilization.0';
$descr = 'Processor';
$usage = trim(snmp_get($device, 'agentSwitchCpuProcessTotalUtilization.0', '-OQUvs', 'DNOS-SWITCHING-MIB'),'" ');

if (substr($usage,0,5) == '5 Sec')
{
  discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'dnos-switching-mib', $descr, '1', $usage);
}

unset($oid, $descr, $usage);

// EOF
