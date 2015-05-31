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

// Old CISCO-ENVMON-MIB

echo(" CISCO-ENVMON-MIB ");

$sensor_type       = 'cisco-envmon';
$sensor_state_type = 'cisco-envmon-state';

// Temperatures:
$oids = snmpwalk_cache_oid($device, 'ciscoEnvMonTemperatureStatusEntry', array(), 'CISCO-ENVMON-MIB', mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = $entry['ciscoEnvMonTemperatureStatusDescr'];
  if ($descr == '') { continue; } // Skip sensors with empty description, seems like Cisco bug

  if (isset($entry['ciscoEnvMonTemperatureStatusValue']))
  {
    $oid = '.1.3.6.1.4.1.9.9.13.1.3.1.3.'.$index;
    // Exclude duplicated entries from CISCO-ENTITY-SENSOR
    $ent_count = dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?) AND CONCAT(`sensor_limit`) = ?;',
                              array($device['device_id'], 'cisco-entity-sensor', 'temperature', $descr.'%', '%- '.$descr, $entry['ciscoEnvMonTemperatureThreshold']));
    if (!$ent_count && $entry['ciscoEnvMonTemperatureStatusValue'] != 0)
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensor_type, $descr, 1,
                      $entry['ciscoEnvMonTemperatureStatusValue'], array('limit_high' => $entry['ciscoEnvMonTemperatureThreshold']));
    }
  }
  else if (isset($entry['ciscoEnvMonTemperatureState']))
  {
    $oid = '.1.3.6.1.4.1.9.9.13.1.3.1.6.'.$index;
    // Exclude duplicated entries from CISCO-ENTITY-SENSOR
    $ent_count = dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?);',
                              array($device['device_id'], 'cisco-entity-state', 'state', $descr.'%', '%- '.$descr));
    //Not numerical values, only states
    $value = state_string_to_numeric($sensor_state_type, $entry['ciscoEnvMonTemperatureState']);
    if (!$ent_count && $value <= 4) // Exclude 'notPresent' and 'notFunctioning'
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'temp-'.$index, $sensor_state_type, $descr, NULL,
                      $value, array('entPhysicalClass' => 'temperature'));
    }
  }
}

// Voltages
$scale = si_to_scale('milli');

$oids = snmpwalk_cache_oid($device, 'ciscoEnvMonVoltageStatusEntry', array(), 'CISCO-ENVMON-MIB', mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = $entry['ciscoEnvMonVoltageStatusDescr'];
  if ($descr == '') { continue; } // Skip sensors with empty description, seems like Cisco bug

  if (isset($entry['ciscoEnvMonVoltageStatusValue']))
  {
    $oid = '.1.3.6.1.4.1.9.9.13.1.2.1.3.'.$index;
    // Exclude duplicated entries from CISCO-ENTITY-SENSOR
    $query = 'SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?) ';
    $query .= ($entry['ciscoEnvMonVoltageThresholdHigh'] > $entry['ciscoEnvMonVoltageThresholdLow']) ? 'AND CONCAT(`sensor_limit`) = ? AND CONCAT(`sensor_limit_low`) = ?;' : 'AND CONCAT(`sensor_limit_low`) = ? AND CONCAT(`sensor_limit`) = ?;'; //swich negative numbers
    $ent_count = dbFetchCell($query, array($device['device_id'], 'cisco-entity-sensor', 'voltage', $descr.'%', '%- '.$descr, $entry['ciscoEnvMonVoltageThresholdHigh'] * $scale, $entry['ciscoEnvMonVoltageThresholdLow'] * $scale));
    if (!$ent_count)
    {
      $limits = array('limit_high' => $entry['ciscoEnvMonVoltageThresholdLow']  * $scale,
                      'limit_low'  => $entry['ciscoEnvMonVoltageThresholdHigh'] * $scale);
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $sensor_type, $descr, $scale,
                      $entry['ciscoEnvMonVoltageStatusValue'] * $scale, $limits);
    }
  }
  else if (isset($entry['ciscoEnvMonVoltageState']))
  {
    $oid   = '.1.3.6.1.4.1.9.9.13.1.2.1.7.'.$index;
    //Not numerical values, only states
    $value = state_string_to_numeric($sensor_state_type, $entry['ciscoEnvMonVoltageState']);
    $query = 'SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?);';
    $ent_count = dbFetchCell($query, array($device['device_id'], 'cisco-entity-state', 'state', $descr.'%', '%- '.$descr));
    if (!$ent_count && $value <= 4) // Exclude 'notPresent' and 'notFunctioning'
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'voltage-'.$index, $sensor_state_type, $descr, NULL,
                      $value, array('entPhysicalClass' => 'voltage'));
    }
  }
}

// Supply
$oids = snmpwalk_cache_oid($device, 'ciscoEnvMonSupplyStatusEntry', array(), 'CISCO-ENVMON-MIB', mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = $entry['ciscoEnvMonSupplyStatusDescr'];
  if ($descr == '') { continue; } // Skip sensors with empty description, seems like Cisco bug

  if (isset($entry['ciscoEnvMonSupplyState']))
  {
    $oid = '.1.3.6.1.4.1.9.9.13.1.5.1.3.'.$index;
    // Exclude duplicated entries from CISCO-ENTITY-SENSOR
    $ent_count = dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?);',
                              array($device['device_id'], 'cisco-entity-state', 'state', $descr.'%', '%- '.$descr));
    //Not numerical values, only states
    $value = state_string_to_numeric($sensor_state_type, $entry['ciscoEnvMonSupplyState']);
    if (!$ent_count && $value <= 4) // Exclude 'notPresent' and 'notFunctioning'
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'supply-'.$index, $sensor_state_type, $descr, NULL,
                      $value, array('entPhysicalClass' => 'power'));
    }
  }
}

// Fans
echo(" Fans ");

$oids = snmpwalk_cache_oid($device, 'ciscoEnvMonFanStatusEntry', array(), 'CISCO-ENVMON-MIB', mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = $entry['ciscoEnvMonFanStatusDescr'];
  if ($descr == '') { continue; } // Skip sensors with empty description, seems like Cisco bug

  if (isset($entry['ciscoEnvMonFanState']))
  {
    $oid = '.1.3.6.1.4.1.9.9.13.1.4.1.3.'.$index;
    // Exclude duplicated entries from CISCO-ENTITY-SENSOR
    $ent_count = dbFetchCell('SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ? AND `sensor_type` = ? AND `sensor_class` = ? AND (`sensor_descr` LIKE ? OR `sensor_descr` LIKE ?);',
                              array($device['device_id'], 'cisco-entity-state', 'state', $descr.'%', '%- '.$descr));
    //Not numerical values, only states
    $value = state_string_to_numeric($sensor_state_type, $entry['ciscoEnvMonFanState']);
    if (!$ent_count && $value <= 4) // Exclude 'notPresent' and 'notFunctioning'
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, 'fan-'.$index, $sensor_state_type, $descr, NULL,
                      $value, array('entPhysicalClass' => 'fan'));
    }
  }
}

// EOF
