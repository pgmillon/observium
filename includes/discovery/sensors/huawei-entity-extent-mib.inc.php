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

$mib = 'HUAWEI-ENTITY-EXTENT-MIB';
echo(" $mib ");

$huawei['sensors_names'] = snmpwalk_cache_oid($device, 'hwEntityBomEnDesc', array(), $mib, mib_dirs('huawei'));
$huawei['temp'] = snmpwalk_cache_oid($device, 'hwEntityTemperature', array(), $mib, mib_dirs('huawei'));
$huawei['fan']  = snmpwalk_cache_oid($device, 'HwFanStatusEntry',  array(), $mib, mib_dirs('huawei'));
$opticalarray  = snmpwalk_cache_oid($device, 'HwOpticalModuleInfoEntry',  $opticalarray, $mib, mib_dirs('huawei'));

//If we got entity-mib, merge it with optical modules
if (is_array($GLOBALS['cache']['entity-mib']))
{
  foreach ($GLOBALS['cache']['entity-mib'] as $index => $entry)
  {
    if (isset($opticalarray[$index]))
    {
      $opticalarray[$index] = array_merge($opticalarray[$index], $entry);
    }
  }
}

foreach ($huawei['temp'] as $index => $entry)
{
  $oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.11.'.$index;
  $descr = explode(',', $huawei['sensors_names'][$index]['hwEntityBomEnDesc']);
  $value = $entry['hwEntityTemperature'];
  if ($entry['hwEntityTemperature'] > 0 && $value <= 1000)
  {
    $options = array('limit_high' => snmp_get($device, "hwEntityTemperatureThreshold.".$index, "-Osqv", "HUAWEI-ENTITY-EXTENT-MIB", mib_dirs('huawei')));
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'huawei', $descr[0], 1, $value, $options);
  }
}
unset($options);

foreach ($huawei['fan'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.2011.5.25.31.1.1.10.1.5.'.$index;
  $fanstateoid = '.1.3.6.1.4.1.2011.5.25.31.1.1.10.1.7.'.$index;
  $value = $entry['hwEntityFanSpeed'];
  $descr = 'Slot '.$entry['hwEntityFanSlot'].' Fan '.$entry['hwEntityFanSn'];
  if ($entry['hwEntityFanSpeed'] > 0)
  {
    discover_sensor($valid['sensor'], 'load', $device, $oid, $index, 'huawei', $descr, 1, $value);
    discover_status($device, $fanstateoid, $index, 'huawei-entity-ext-mib-fan-state', $descr, $entry['hwEntityFanState'], array('entPhysicalClass' => 'fan'));
  }
}

foreach ($opticalarray as $index => $entry)
{
  if ($entry['entPhysicalClass'] === 'port')
  {
    // Port found, get mapped ifIndex
    $sensor_port = $opticalarray[$index];
    if (isset($sensor_port['0']['entAliasMappingIdentifier']) && strpos($sensor_port['0']['entAliasMappingIdentifier'], "fIndex"))
    {
      list(, $ifIndex) = explode(".", $sensor_port['0']['entAliasMappingIdentifier']);
      $port = get_port_by_index_cache($device['device_id'], $ifIndex);
      $temperatureoid = '.1.3.6.1.4.1.2011.5.25.31.1.1.3.1.5.'.$index;
      $voltageoid = '.1.3.6.1.4.1.2011.5.25.31.1.1.3.1.6.'.$index;
      $biascurrentoid = '.1.3.6.1.4.1.2011.5.25.31.1.1.3.1.7.'.$index;
      $rxpoweroid = '.1.3.6.1.4.1.2011.5.25.31.1.1.3.1.8.'.$index;
      $txpoweroid = '.1.3.6.1.4.1.2011.5.25.31.1.1.3.1.9.'.$index;
      //Optical module name
      $descr = $entry['hwEntityOpticalVenderPn'];
      //If no part name found use serial number for description
      if (empty($entry['hwEntityOpticalVenderPn']))
      {
        $descr = $entry['hwEntityOpticalVenderSn'];
      }

      $options['entPhysicalClass'] = $entry['entPhysicalClass'];
      $options['entPhysicalIndex_measured'] = $ifIndex;
      $options['measured_class']  = 'port';
      $options['measured_entity'] = $port['port_id'];

      //Ignore optical sensors with temperature of zero or negative
      if ($entry['hwEntityOpticalTemperature'] > 1)
      {
        discover_sensor($valid['sensor'], 'temperature', $device, $temperatureoid, $index, 'huawei', 'Module Temperature', 1, $entry['hwEntityOpticalTemperature'], $options);
        discover_sensor($valid['sensor'], 'voltage', $device, $voltageoid, $index, 'huawei', 'Module Voltage', 0.001, $entry['hwEntityOpticalVoltage'], $options);
        discover_sensor($valid['sensor'], 'current', $device, $biascurrentoid, $index, 'huawei', 'Bias Current ', 0.000001, $entry['hwEntityOpticalBiasCurrent'], $options);
        //Huawei does not follow their own MIB for some devices and instead reports Rx/Tx Power as dBm converted to mW then multiplied by 1000
        if ($entry['hwEntityOpticalRxPower'] >= 0)
        {
          discover_sensor($valid['sensor'], 'power', $device, $rxpoweroid, 'hwEntityOpticalRxPower.' . $index, 'huawei', 'Rx Power', 0.000001, $entry['hwEntityOpticalRxPower'], $options);
          discover_sensor($valid['sensor'], 'power', $device, $txpoweroid, 'hwEntityOpticalTxPower.' . $index, 'huawei', 'Tx Power', 0.000001, $entry['hwEntityOpticalTxPower'], $options);
        } else {
          discover_sensor($valid['sensor'], 'dbm', $device, $rxpoweroid, 'hwEntityOpticalRxPower.' . $index, 'huawei', 'Rx Power', 0.01, $entry['hwEntityOpticalRxPower'], $options);
          discover_sensor($valid['sensor'], 'dbm', $device, $txpoweroid, 'hwEntityOpticalTxPower.' . $index, 'huawei', 'Tx Power', 0.01, $entry['hwEntityOpticalTxPower'], $options);
        }
      }
    }
  }
}

// EOF
