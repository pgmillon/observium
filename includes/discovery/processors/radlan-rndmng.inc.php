<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Hardcoded discovery of cpu usage on RADLAN devices.

echo(" RADLAN-rndMng ");

$descr = "Processor";
$usage = snmp_get($device, ".1.3.6.1.4.1.89.1.9.0", "-OQUvs", "RADLAN-rndMng", mib_dirs('dell'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.89.1.9.0", 0, "radlan", $descr, 1, $usage, NULL, NULL);
}

// EOF
