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

echo("ASYNCOS-MAIL-MIB ");

$descr = "Processor";
$cpu = snmp_get($device, "ASYNCOS-MAIL-MIB::perCentCPUUtilization.0", "-Ovq");

if (is_numeric($cpu))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.15497.1.1.1.2.0", "0", "asyncos-cpu", $descr, "1", $cpu, NULL, NULL);
}

// EOF
