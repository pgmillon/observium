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

echo(" MS-SWITCH30-MIB ");

$value = trim(snmp_get($device, "deviceTemperature.0", "-Ovq", "MS-SWITCH30-MIB"), '"');
$oid   = ".1.3.6.1.4.1.3181.10.3.1.9";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', 'microsens', 'wireway', 1, $value);
}

// EOF
