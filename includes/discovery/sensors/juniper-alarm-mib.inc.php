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

echo(" JUNIPER-ALARM-MIB ");

$value = state_string_to_numeric('juniper-alarm-state', snmp_get($device, "jnxYellowAlarmState.0", "-Oqv", "JUNIPER-ALARM-MIB", mib_dirs('junos')));

if (is_numeric($value) && $value > 0)
{
  $descr = "Yellow Alarm";
  $oid   = ".1.3.6.1.4.1.2636.3.4.2.2.1.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "jnxYellowAlarmState.0", 'juniper-alarm-state', $descr, NULL, $value);
}

$value = state_string_to_numeric('juniper-alarm-state', snmp_get($device, "jnxRedAlarmState.0", "-Oqv", "JUNIPER-ALARM-MIB", mib_dirs('junos')));

if (is_numeric($value) && $value > 0)
{
  $descr = "Red Alarm";
  $oid   = ".1.3.6.1.4.1.2636.3.4.2.3.1.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "jnxRedAlarmState.0", 'juniper-alarm-state', $descr, NULL, $value);
}

// EOF
