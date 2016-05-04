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

$mib = 'WebGraph-Thermo-Hygrometer-US-MIB';
echo(" $mib ");

//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroBinaryTempValue.1 = INTEGER: 266
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroBinaryTempValue.2 = INTEGER: 587
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortName.1 = STRING: "Temperatur"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortName.2 = STRING: "rel. Feuchte"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortText.1 = STRING: "Sensorbeschreibung 1"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortText.2 = STRING: "Sensorbeschreibung 2"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortSensorSelect.1 = Hex-STRING: 00 00 00 02
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroPortSensorSelect.2 = Hex-STRING: 00 00 00 01

//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmMin.1 = STRING: "10"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmMax.1 = STRING: "25"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmRHMin.1 = STRING: "10"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmRHMax.1 = STRING: "85"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmAHMin.1 = STRING: "1"
//WebGraph-Thermo-Hygrometer-US-MIB::wtWebGraphThermHygroAlarmAHMax.1 = STRING: "25"

$oids = snmpwalk_cache_multi_oid($device, "wtWebGraphThermHygroBinaryTempValueTable", array(), $mib);
if ($GLOBALS['snmp_status'])
{
  $oids = snmpwalk_cache_multi_oid($device, "wtWebGraphThermHygroPortTable",         $oids, $mib);

  // Temperature
  if (is_numeric($oids[1]['wtWebGraphThermHygroBinaryTempValue']))
  {
    $descr   = $oids[1]['wtWebGraphThermHygroPortName'];
    $oid     = '.1.3.6.1.4.1.5040.1.2.9.1.4.1.1.1';
    $value   = $oids[1]['wtWebGraphThermHygroBinaryTempValue'];

    $limits  = snmp_get_multi($device, 'wtWebGraphThermHygroAlarmMin.1 wtWebGraphThermHygroAlarmMax.1', '-OQUs', $mib);
    $limits['limit_high'] = trim($limits[1]['wtWebGraphThermHygroAlarmMax'], ' "');
    $limits['limit_low']  = trim($limits[1]['wtWebGraphThermHygroAlarmMin'], ' "');
    $options = array('limit_high' => (is_numeric($limits['limit_high']) ? $limits['limit_high'] : NULL),
                     'limit_low'  => (is_numeric($limits['limit_low'])  ? $limits['limit_low']  : NULL));

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'wtWebGraphThermHygroBinaryTempValue.1', 'wut', $descr, 0.1, $value, $options);
  }

  // Humidity/Volts
  if (is_numeric($oids[2]['wtWebGraphThermHygroBinaryTempValue']))
  {
    // Binary coded options for sensor 2:
    //        Octet 1: unused
    //        Octet 2: unused
    //        Octet 3: unused
    //        Octet 4:
    //                Bit 0  :        W&T Sensor rel. humidity (default)
    //                Bit 1  :        Skalar 0-2.5V
    //                Bit 2  :        Disconnect
    //   Bit 3-7:        unused"
    list(,,,$octet) = explode(' ', $oids[2]['wtWebGraphThermHygroPortSensorSelect']);

    $descr = $oids[2]['wtWebGraphThermHygroPortName'];
    $oid   = '.1.3.6.1.4.1.5040.1.2.9.1.4.1.1.2';
    $value = $oids[2]['wtWebGraphThermHygroBinaryTempValue'];

    if ($octet == "01")
    {
      // Humidity
      $limits  = snmp_get_multi($device, 'wtWebGraphThermHygroAlarmRHMin.1 wtWebGraphThermHygroAlarmRHMax.1', '-OQUs', $mib);
      $limits['limit_high'] = trim($limits[1]['wtWebGraphThermHygroAlarmRHMax'], ' "');
      $limits['limit_low']  = trim($limits[1]['wtWebGraphThermHygroAlarmRHMin'], ' "');
      $options = array('limit_high' => (is_numeric($limits['limit_high']) ? $limits['limit_high'] : NULL),
                       'limit_low'  => (is_numeric($limits['limit_low'])  ? $limits['limit_low']  : NULL));

      discover_sensor($valid['sensor'], 'humidity', $device, $oid, 'wtWebGraphThermHygroBinaryTempValue.2', 'wut', $descr, 0.1, $value, $options);
    }
    else if ($octet == "02")
    {
      // Voltage? Not tested
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, 'wtWebGraphThermHygroBinaryTempValue.2', 'wut', $descr, 0.1, $value);
    }
  }
}

unset($oids, $oid, $descr, $options, $limits, $value);

// EOF
