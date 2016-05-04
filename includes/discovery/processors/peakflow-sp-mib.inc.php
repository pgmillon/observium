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

//PEAKFLOW-SP-MIB::deviceCpuLoadAvg5min.0 = INTEGER: 11

echo("PEAKFLOW-SP-MIB ");

$descr = "Processor";
$oid   = ".1.3.6.1.4.1.9694.1.4.2.1.2.0";
$usage = snmp_get($device, "deviceCpuLoadAvg5min.0", "-OUQnv", "PEAKFLOW-SP-MIB");

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, 0, "peakflow-sp-mib", $descr, 1, $usage);
}

unset ($descr, $oid, $usage);

// EOF
