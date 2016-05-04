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

//  Hardcoded discovery of cpu usage on Dell Powerconnect devices.
//
//  Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.4.0 = STRING: "5 Sec (6.99%),    1 Min (6.72%),   5 Min (9.06%)"

echo("Dell-Vendor-MIB ");

$descr = "Processor";
$usage = trim(snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.4.0", "-OQUvs", "Dell-Vendor-MIB", mib_dirs('dell')),'"');

if (substr($usage,0,5) == "5 Sec")
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.4.0", "0", "powerconnect", $descr, "1", $usage, NULL, NULL);
}

// EOF
