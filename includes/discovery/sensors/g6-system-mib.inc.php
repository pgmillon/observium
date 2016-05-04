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

echo(" G6-SYSTEM-MIB ");

$value = snmp_get($device, "systemTemperature.0", "-Ovq", "G6-SYSTEM-MIB", mib_dirs('microsens-g6'));
$oid   = ".1.3.6.1.4.1.3181.10.6.1.30.104.0";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', 'microsens', 'wireway', 1, $value);
}

unset($data, $oid, $descr, $limits, $value);

// EOF
