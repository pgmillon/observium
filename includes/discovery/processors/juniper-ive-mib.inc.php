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

echo("JUNIPER-IVE-MIB ");

$percent = snmp_get($device, ".1.3.6.1.4.1.12532.10.0", "-OQv", "JUNIPER-IVE-MIB");

if (is_numeric($percent))
{
  discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.12532.10.0", "1", "juniperive", "CPU Utilization", "1", $percent, NULL, NULL);
}

unset($percent);

// EOF
