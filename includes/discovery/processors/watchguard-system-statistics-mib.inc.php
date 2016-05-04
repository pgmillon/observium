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

// Hardcoded discovery of cpu usage on WatchGuard devices.
//
// WATCHGUARD-SYSTEM-STATISTICS-MIB::wgSystemCpuUtil5.0 = COUNTER: 123

echo("WATCHGUARD-SYSTEM-STATISTICS-MIB ");

$descr = "Processor";
$usage = snmp_get($device, ".1.3.6.1.4.1.3097.6.3.78.0", "-OQUvs", "WATCHGUARD-SYSTEM-STATISTICS-MIB", $config['mib_dir'].':'.mib_dirs('watchguard'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, "1.3.6.1.4.1.3097.6.3.78.0", "0", "firebox-fixed", $descr, "100", $usage, NULL, NULL);
}

// EOF
