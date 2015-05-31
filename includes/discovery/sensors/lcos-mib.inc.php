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

echo(" LCOS-MIB ");

$value = trim(snmp_get($device, "lcsStatusHardwareInfoTemperatureDegrees.0", "-Ovq", "LCOS-MIB"), '"');
$oid   = ".1.3.6.1.4.1.2356.11.1.47.20.0";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 0, 'lcos', $descr , 1, $value);
}

// EOF
