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

//AIRESPACE-SWITCHING-MIB::agentCurrentCPUUtilization.0 = 0
$usage = snmp_get($device, 'agentCurrentCPUUtilization.0', '-OQUvs', $mib);
$descr = "Processor";
$oid   = ".1.3.6.1.4.1.14179.1.1.5.1.0";

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, 0, $mib, $descr, 1, $usage);
}

unset ($usage, $descr, $oid);

// EOF
