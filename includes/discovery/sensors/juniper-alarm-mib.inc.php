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

echo(" JUNIPER-ALARM-MIB ");

$value = snmp_get($device, "jnxYellowAlarmState.0", "-Oqv", "JUNIPER-ALARM-MIB");

if ($value !== '')
{
  $descr = "Yellow Alarm";
  $oid   = ".1.3.6.1.4.1.2636.3.4.2.2.1.0";

  rename_rrd($device, 'status-juniper-alarm-state-jnxYellowAlarmState.0', 'status-juniper-yellow-state-jnxYellowAlarmState.0');
  discover_sensor($valid['sensor'], 'state', $device, $oid, "jnxYellowAlarmState.0", 'juniper-yellow-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

$value = snmp_get($device, "jnxRedAlarmState.0", "-Oqv", "JUNIPER-ALARM-MIB");

if ($value !== '')
{
  $descr = "Red Alarm";
  $oid   = ".1.3.6.1.4.1.2636.3.4.2.3.1.0";

  discover_sensor($valid['sensor'], 'state', $device, $oid, "jnxRedAlarmState.0", 'juniper-alarm-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// EOF
