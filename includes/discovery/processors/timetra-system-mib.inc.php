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

//TIMETRA-SYSTEM-MIB::sgiCpuUsage.0 = Gauge32: 42 percent

echo("TIMETRA-SYSTEM-MIB ");

$descr = "Processor";
$oid   = ".1.3.6.1.4.1.6527.3.1.2.1.1.1.0";
$usage = snmp_get($device, "sgiCpuUsage.0", "-OUQnv", "TIMETRA-SYSTEM-MIB");

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, 0, "timetra-system-mib", $descr, 1, $usage);
}

unset ($descr, $oid, $usage);

// EOF
