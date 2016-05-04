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

$mib = 'CHECKPOINT-MIB';

echo(" $mib ");

$chkpnt['temp'] = snmpwalk_cache_oid($device, 'tempertureSensorEntry', array(), $mib);
$chkpnt['fan']  = snmpwalk_cache_oid($device, 'fanSpeedSensorEntry',  array(), $mib);
$chkpnt['volt'] = snmpwalk_cache_oid($device, 'voltageSensorEntry', array(), $mib);

foreach ($chkpnt['temp'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.2620.1.6.7.8.1.1.3.'.$index;
  $descr = $entry['tempertureSensorName'];
  $value = $entry['tempertureSensorValue'];
  if ($entry['tempertureSensorValue'] > 0 && $value <= 1000)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'checkpoint', $descr, 1, $value);
  }
}

foreach ($chkpnt['fan'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.2620.1.6.7.8.2.1.3.'.$index;
  $descr = $entry['fanSpeedSensorName'];
  $value = $entry['fanSpeedSensorValue'];
  if ($entry['fanSpeedSensorValue'] > 0)
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'checkpoint', $descr, 1, $value);
  }
}

foreach ($chkpnt['volt'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.2620.1.6.7.8.3.1.3.'.$index;
  $descr = $entry['voltageSensorName'];
  $value = $entry['voltageSensorValue'];
  if (is_numeric($value) && $value > 0)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'checkpoint', $descr, 1, $value);
  }
}

// HA state
# CHECKPOINT-MIB::haProdName.0 = STRING: High Availability
# CHECKPOINT-MIB::haStarted.0 = STRING: yes
# CHECKPOINT-MIB::haState.0 = STRING: standby
# CHECKPOINT-MIB::haStatCode.0 = INTEGER: 0
$chkpnt['ha'] = snmp_get_multi($device, 'haProdName.0 haStarted.0 haState.0 haStatCode.0', '-OQUs', $mib);

if (isset($chkpnt['ha'][0]) && $chkpnt['ha'][0]['haStarted'] == 'yes')
{
  $descr = $chkpnt['ha'][0]['haProdName'].' ('.$chkpnt['ha'][0]['haState'].')';
  $oid   = '.1.3.6.1.4.1.2620.1.5.101.0';
  $value = $chkpnt['ha'][0]['haStatCode'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'haStatCode.0', 'checkpoint-ha-state', $descr, NULL, $value, array('entPhysicalClass' => 'other'));
}

// EOF
