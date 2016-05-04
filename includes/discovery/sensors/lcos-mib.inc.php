<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo(" LCOS-MIB ");

$value = snmp_get($device, "lcsStatusHardwareInfoTemperatureDegrees.0", "-Ovq", "LCOS-MIB", mib_dirs('lancom'));
$oid   = ".1.3.6.1.4.1.2356.11.1.47.20.0";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', 'lcsStatusHardwareInfoTemperatureDegrees', 'Hardware Temperature', 1, $value);
}

// EOF
