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

$value = trim(snmp_get($device, "rfTemp.0", "-Ovq", "TRANGO-APEX-RF-MIB", mib_dirs('trango')), '"');
$oid   = ".1.3.6.1.4.1.5454.1.60.3.7.0";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 0, 'trango-apex-rf-mib', "RF Temperature" , 1, $value);
}

$value = trim(snmp_get($device, "rssi.0", "-Ovq", "TRANGO-APEX-RF-MIB", mib_dirs('trango')), '"');
$oid   = ".1.3.6.1.4.1.5454.1.60.3.9.0";

if (is_numeric($value))
{
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 0, 'trango-apex-rf-mib', "RF RSSI" , 1, $value);
}

// EOF
