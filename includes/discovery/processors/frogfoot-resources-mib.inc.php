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

//  Hardcoded discovery of cpu usage on Ubiquiti AirOS devices.
//
//  FROGFOOT-RESOURCES-MIB::loadValue.2 = Gauge32: 0

echo("FROGFOOT-RESOURCES-MIB ");

$descr = "Processor";
$usage = snmp_get($device, "loadValue.2", "-OQUvs", "FROGFOOT-RESOURCES-MIB");
if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.10002.1.1.1.4.2.1.3.2", "0", "ubiquiti-fixed", $descr, "1", $usage, NULL, NULL);
}

// EOF
