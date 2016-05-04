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

// Hardcoded discovery of cpu usage on SmartEdge devices.
//
// RBN-CPU-METER-MIB::rbnCpuMeterFiveMinuteAvg.0

echo("RBN-CPU-METER-MIB ");

$descr = "Processor";
$usage = snmp_get($device, ".1.3.6.1.4.1.2352.2.6.1.3.0", "-Ovq");

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.2352.2.6.1.3.0", "0", "seos", $descr, "1", $usage, NULL, NULL);
}

// EOF
