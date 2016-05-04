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

echo("FE-FIREEYE-MIB");

// FIXME ew ew. Rewrite!

// Temperatures
$oids = snmp_walk($device, "feTemperatureValue", "-Osqn", "FE-FIREEYE-MIB", mib_dirs('fireeye'));
foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  $tmp = explode(" ", $data);
  $index = str_replace('.1.3.6.1.4.1.25597.11.1.1.4.','',$tmp[0]);
  discover_sensor($valid['sensor'], "temperature", $device, $tmp[0], $index, 'fireeye', "Temperature $index", 1, $tmp[1]);

}

// Fans
$oids = snmp_walk($device, "feFanSpeed", "-Osqn", "FE-FIREEYE-MIB", mib_dirs('fireeye'));
foreach (explode("\n", $oids) as $data) {
  $data = trim($data);
  $tmp = explode(" ", $data);
  $index = str_replace('.1.3.6.1.4.1.25597.11.4.1.3.1.4.','',$tmp[0]);
  discover_sensor($valid['sensor'], "fanspeed", $device, $tmp[0], $index, 'fireeye', "Fan $index", 1, $tmp[1]);
}

// EOF
