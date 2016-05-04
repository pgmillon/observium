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

$mib = 'DNOS-BOXSERVICES-PRIVATE-MIB';
echo(" $mib ");

// Temperature

// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMin.0 = INTEGER: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMax.0 = INTEGER: 45

$boxServicesNormalTempRangeMin = snmp_get($device, 'boxServicesNormalTempRangeMin.0', '-Ovq', $mib);
$boxServicesNormalTempRangeMax = snmp_get($device, 'boxServicesNormalTempRangeMax.0', '-Ovq', $mib);

// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.1.0 = Gauge32: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.1.1 = Gauge32: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.2.0 = Gauge32: 2
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.2.1 = Gauge32: 2
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.1.0 = Gauge32: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.1.1 = Gauge32: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.2.0 = Gauge32: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.2.1 = Gauge32: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.1.0 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.1.1 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.2.0 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.2.1 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.1.0 = INTEGER: 35
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.1.1 = INTEGER: 29
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.2.0 = INTEGER: 33
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.2.1 = INTEGER: 28

$oid  = 'boxServicesTempSensorsTable';
$oids = snmpwalk_cache_multi_oid($device, $oid, array(), $mib);

foreach ($oids as $index => $entry)
{
  list($unit, $iter) = explode('.', $index);

  // Temperature
  $descr = "Unit $unit Temperature Sensor ". ($iter+1);
  $value = $entry['boxServicesTempSensorTemperature'];

  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.5.$index";
  $options    = array(
    'limit_low'        => $boxServicesNormalTempRangeMin,
    'limit_high'       => $boxServicesNormalTempRangeMax,
    'entPhysicalClass' => 'temperature',
  );

  discover_sensor($valid['sensor'], 'temperature', $device, $sensor_oid, "boxServicesTempSensorTemperature.$index", $mib, $descr, 1, $value, $options);

  // State
  $value = $entry['boxServicesTempSensorState'];
  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.4.$index";
  $options    = array('entPhysicalClass' => 'temperature');

  discover_sensor($valid['sensor'], 'state', $device, $sensor_oid, "boxServicesTempSensorState.$index", 'dnos-boxservices-temp-state', $descr, NULL, $value, $options);
}

// Unit / Stack Member Temperature State
//   Some devices (N2048 v6.0.1.3) don't provide state data for each sensor
//   in the boxServicesTempSensorsTable we walk above, but they do provide
//   overall state data for the Unit / Stack Member.
//
// boxServicesTempUnitState.1 = normal
// boxServicesTempUnitState.2 = normal
// boxServicesTempUnitState.3 = normal

$oid  = 'boxServicesTempUnitState';
$oids = snmpwalk_cache_multi_oid($device, $oid, array(), $mib);

foreach ($oids as $index => $entry)
{
  $descr = "Unit $index Temperature State";
  $value = $entry['boxServicesTempUnitState'];

  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.15.1.2.$index";
  $options    = array('entPhysicalClass' => 'temperature');

  discover_sensor($valid['sensor'], 'state', $device, $sensor_oid, "boxServicesTempUnitState.$index", 'dnos-boxservices-temp-state', $descr, NULL, $value, $options);
}

// Fans
//
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.0 = INTEGER: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.1 = INTEGER: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemType.0 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemType.1 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.0 = INTEGER: operational(2)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.1 = INTEGER: operational(2)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.0 = INTEGER: 9056
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.1 = INTEGER: 9230
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.0 = INTEGER: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.1 = INTEGER: 0

$oid  = 'boxServicesFansTable';
$oids = snmpwalk_cache_multi_oid($device, $oid, array(), $mib);
$show_numbers = count($oids) > 1;

foreach ($oids as $index => $entry)
{
  if ($entry['boxServicesFanItemState'] == 'notpresent') { continue; }

  // State Sensor
  $value = $entry['boxServicesFanItemState'];
  $descr = nicecase(rewrite_entity_name($entry['boxServicesFanItemType'])) .' Fan';

  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.6.1.3.$index";
  $options    = array('entPhysicalClass' => 'fan');

  if ($show_numbers) { $descr .= ' '. ($index+1); }

  discover_sensor($valid['sensor'], 'state', $device, $sensor_oid, "boxServicesFanItemState.$index", 'dnos-boxservices-state', $descr, NULL, $value, $options);
}

// Power Supplies
//
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyIndex.0 = INTEGER: 0
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyIndex.1 = INTEGER: 1
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemType.0 = INTEGER: fixed(1)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemType.1 = INTEGER: removable(2)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemState.0 = INTEGER: operational(2)
// DNOS-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemState.1 = INTEGER: notpresent(1)

$oid  = 'boxServicesPowSuppliesTable';
$oids = snmpwalk_cache_multi_oid($device, $oid, array(), $mib);
$show_numbers = count($oids) > 1;

foreach ($oids as $index => $entry)
{
  if ($entry['boxServicesPowSupplyItemState'] == 'notpresent') { continue; }

  // State Sensor
  $value = $entry['boxServicesPowSupplyItemState'];
  $descr = nicecase(rewrite_entity_name($entry['boxServicesPowSupplyItemType'])) .' PSU';
  if ($show_numbers) { $descr .= ' '. ($index+1); }

  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.7.1.3.$index";
  $options    = array('entPhysicalClass' => 'power');

  discover_sensor($valid['sensor'], 'state', $device, $sensor_oid, "boxServicesPowSupplyState.$index", 'dnos-boxservices-state', $descr, NULL, $value, $options);
}

// Power Usage
//
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.1.1 = STRING: "6d:03:46:39"
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.1.60 = STRING: "3d:16:45:40"
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.2.1 = STRING: "6d:04:46:39"
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.2.60 = STRING: "3d:17:45:40"
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.3.1 = STRING: "6d:04:46:39"
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitSampleTime.3.60 = STRING: "3d:17:45:40"
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.1.1 = INTEGER: 32616
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.1.60 = INTEGER: 32616
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.2.1 = INTEGER: 0
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.2.60 = INTEGER: 28992
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.3.1 = INTEGER: 0
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryUnitPowerConsumption.3.60 = INTEGER: 32616
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.1.1 = INTEGER: 94224
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.1.60 = INTEGER: 90600
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.2.1 = INTEGER: 0
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.2.60 = INTEGER: 94224
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.3.1 = INTEGER: 0
// ...
// DNOS-BOXSERVICES-PRIVATE-MIB::boxsPwrUsageHistoryStackPowerConsumption.3.60 = INTEGER: 94224

$oid  = 'boxsUnitPwrUsageHistoryTable';
$oids = snmpwalk_cache_multi_oid($device, $oid, array(), $mib);

// This may not hold up in the long run, but...
// Assume:
// 1. each unit has the same number of samples
// 2. the newest samples are in the highest numbered sample index
// 3. we will graph the most recent sample even if it's hours old
//   (See DNOS CLI "power-usage-history sampling-interval" command)
//
// Procedure:
// 1. Move array pointer to end of array.  (ex. key = "3.60")
// 2. Pull the samples per unit off the key/index.  (ex. "60")
end($oids);
list(,$samples_per_unit) = explode('.', key($oids));

foreach ($oids as $index => $entry)
{
  list($unit, $sample) = explode('.', $index);
  if (intval($sample) != $samples_per_unit) { continue; }

  $descr = "Unit $unit Power Usage";
  $value = $entry['boxsPwrUsageHistoryUnitPowerConsumption'];

  $sensor_oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.9.1.4.$index";
  $options    = array('entPhysicalClass' => 'power');

  if (is_numeric($value) && $value)
  {
    discover_sensor($valid['sensor'], 'power', $device, $sensor_oid, "boxsPwrUsageHistoryUnitPowerConsumption.$unit", $mib, $descr, 0.001, $value, $options);
  }
}

// EOF
