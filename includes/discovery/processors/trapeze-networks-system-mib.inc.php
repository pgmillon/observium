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

// Hardcoded discovery of cpu usage on trapeze (Juniper wireless) devices.
//
// TRAPEZE-NETWORKS-SYSTEM-MIB::trpzSysCpuLastMinuteLoad.0 = COUNTER: 100

echo("TRAPEZE-NETWORKS-SYSTEM-MIB ");

$descr = "Processor";
$usage = snmp_get($device, ".1.3.6.1.4.1.14525.4.8.1.1.11.2.0", "-OQUvs", "TRAPEZE-NETWORKS-SYSTEM-MIB", $config['mib_dir'].':'.mib_dirs('trapeze'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, "1.3.6.1.4.1.14525.4.8.1.1.11.2.0", "0", "cpu", $descr, "1", $usage, NULL, NULL);
}

// EOF
