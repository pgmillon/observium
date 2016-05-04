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

echo("SW-MIB ");

$descr = "CPU";
$usage = snmp_get($device, "swCpuUsage.0", "-Ovq", 'SW-MIB', mib_dirs('brocade'));

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, "1.3.6.1.4.1.1588.2.1.1.1.26.1", "0", "nos", $descr, "1", $usage, NULL, NULL);
}

// EOF
