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

// FIXME - maybe this one should also be using table walks instead of $outlet_index and $outletsuffix hacks. (but I don't have a device) -TL

echo(" PDU-MIB ");

/////////////////////////////////
// Check for per-outlet polling
$outlet_oids = snmp_walk($device, "outletIndex", "-Osqn", "PDU-MIB");
$outlet_oids = trim($outlet_oids);

if ($outlet_oids) echo("PDU Outlet ");

$ratedvoltage = snmp_get($device,"ratedVoltage.0", "-Ovq", "PDU-MIB");

foreach (explode("\n", $outlet_oids) as $outlet_data)
{
  $outlet_data = trim($outlet_data);
  if ($outlet_data)
  {
    $scale = 0.001;
    list($outlet_oid,$outlet_descr) = explode(" ", $outlet_data,2);
    $outlet_split_oid = explode('.',$outlet_oid);
    $outlet_index = $outlet_split_oid[count($outlet_split_oid)-1];

    $outletsuffix = "$outlet_index";
    $outlet_insert_index = $outlet_index;

    // outletLoadValue: "A non-negative value indicates the measured load in milli Amps"
    $outlet_oid     = ".1.3.6.1.4.1.13742.4.1.2.2.1.4.$outletsuffix";
    $outlet_descr   = snmp_get($device,"outletLabel.$outletsuffix", "-Ovq", "PDU-MIB");
    $limits         = array('limit_high'      => snmp_get($device,"outletCurrentUpperCritical.$outletsuffix", "-Ovq", "PDU-MIB") * $scale,
                            'limit_low'       => snmp_get($device,"outletCurrentLowerCritical.$outletsuffix", "-Ovq", "PDU-MIB") * $scale,
                            'limit_high_warn' => snmp_get($device,"outletCurrentUpperWarning.$outletsuffix",  "-Ovq", "PDU-MIB") * $scale,
                            'limit_low_warn'  => snmp_get($device,"outletCurrentLowerWarning.$outletsuffix",  "-Ovq", "PDU-MIB") * $scale);
    $outlet_current = snmp_get($device,"outletCurrent.$outletsuffix", "-Ovq", "PDU-MIB");

    if ($outlet_current >= 0)
    {
      discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $scale, $outlet_current, $limits);
    }

    $outlet_oid     = ".1.3.6.1.4.1.13742.4.1.2.2.1.8.$outletsuffix";
    $outlet_descr   = snmp_get($device,"outletLabel.$outletsuffix", "-Ovq", "PDU-MIB");
    $limits         = array('limit_high'      => intval((snmp_get($device,"outletCurrentUpperCritical.$outletsuffix", "-Ovq", "PDU-MIB") * $scale) * $ratedvoltage),
                            'limit_low'       => intval((snmp_get($device,"outletCurrentLowerCritical.$outletsuffix", "-Ovq", "PDU-MIB") * $scale) * $ratedvoltage),
                            'limit_high_warn' => intval((snmp_get($device,"outletCurrentUpperWarning.$outletsuffix",  "-Ovq", "PDU-MIB") * $scale) * $ratedvoltage),
                            'limit_low_warn'  => intval((snmp_get($device,"outletCurrentLowerWarning.$outletsuffix",  "-Ovq", "PDU-MIB") * $scale) * $ratedvoltage));
    $outlet_current = snmp_get($device,"outletApparentPower.$outletsuffix", "-Ovq", "PDU-MIB");

    if ($outlet_current >= 0)
    {
      // FIXME. can be apower? and why limits with scale, but current without?
      discover_sensor($valid['sensor'], 'power', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, 1, $outlet_current, $limits);
    }
  } // if ($outlet_data)
} // foreach (explode("\n", $outlet_oids) as $outlet_data)

$scale          = 0.1;
$outlet_oid     = ".1.3.6.1.4.1.13742.4.1.3.1.5.0";
$outlet_descr   = "CPU Temperature";
$limits         = array('limit_high'      => snmp_get($device,"unitTempUpperCritical.0", "-Ovq", "PDU-MIB"),
                        'limit_low'       => snmp_get($device,"unitTempLowerCritical.0", "-Ovq", "PDU-MIB"),
                        'limit_high_warn' => snmp_get($device,"unitTempUpperWarning.0",  "-Ovq", "PDU-MIB"),
                        'limit_low_warn'  => snmp_get($device,"unitTempLowerWarning.0",  "-Ovq", "PDU-MIB"));
$outlet_current = snmp_get($device,"unitCpuTemp.0", "-Ovq", "PDU-MIB"); // Yeah, current scale is different from limits...

if ($outlet_current >= 0)
{
  discover_sensor($valid['sensor'], 'temperature', $device, $outlet_oid, 0, 'raritan', $outlet_descr, $scale, $outlet_current, $limits);
}

$scale          = 0.001;
$outlet_oid     = ".1.3.6.1.4.1.13742.4.1.3.1.2.0";
$outlet_descr   = "Input Feed";
$limits         = array('limit_high'      => snmp_get($device,"unitOrLineVoltageUpperCritical.0", "-Ovq", "PDU-MIB") * $scale,
                        'limit_low'       => snmp_get($device,"unitOrLineVoltageLowerCritical.0", "-Ovq", "PDU-MIB") * $scale,
                        'limit_high_warn' => snmp_get($device,"unitOrLineVoltageUpperWarning.0",  "-Ovq", "PDU-MIB") * $scale,
                        'limit_low_warn'  => snmp_get($device,"unitOrLineVoltageLowerWarning.0",  "-Ovq", "PDU-MIB") * $scale);
$outlet_current = snmp_get($device,"unitVoltage.0", "-Ovq", "PDU-MIB");

if ($outlet_current >= 0)
{
  discover_sensor($valid['sensor'], 'voltage', $device, $outlet_oid, 0, 'raritan', $outlet_descr, $scale, $outlet_current, $limits);
}

// Raritan External Environmental Sensors
$oids = snmpwalk_cache_multi_oid($device, "externalSensorTable", array(), "PDU-MIB");

// PDU-MIB::sensorID.1 = INTEGER: 1
// PDU-MIB::sensorID.2 = INTEGER: 2
// PDU-MIB::externalSensorType.1 = INTEGER: humidity(11)
//   <LIST OF TYPES: rmsCurrent(1), peakCurrent(2), unbalancedCurrent(3), rmsVoltage(4), activePower(5), apparentPower(6), powerFactor(7),
//     activeEnergy(8), apparentEnergy(9), temperature(10), humidity(11), airFlow(12), airPressure(13), onOff(14), trip(15),
//     vibration(16), waterDetection(17), smokeDetection(18), binary(19), contact(20), other(30), none(31)>
// PDU-MIB::externalSensorType.2 = INTEGER: temperature(10)
// PDU-MIB::externalSensorSerialNumber.1 = STRING: <AEI#######>
// PDU-MIB::externalSensorSerialNumber.2 = STRING: <AEI#######>
// PDU-MIB::externalSensorName.1 = STRING: <NAME ASSIGNED VIA WEB>
// PDU-MIB::externalSensorName.2 = STRING: <NAME ASSIGNED VIA WEB>
// PDU-MIB::externalSensorChannelNumber.1 = INTEGER: 1
// PDU-MIB::externalSensorChannelNumber.2 = INTEGER: 1
// PDU-MIB::externalSensorUnits.1 = INTEGER: percent(9)
// <LIST OF UNITS: none(-1), other(0), volt(1), amp(2), watt(3), voltamp(4), wattHour(5), voltampHour(6), degreeC(7), hertz(8), percent(9),
//     meterpersec(10), pascal(11), psi(12), g(13), degreeF(14), feet(15), inches(16), cm(17), meters(18)>
// PDU-MIB::externalSensorUnits.2 = INTEGER: degreeC(7)
// PDU-MIB::externalSensorDecimalDigits.1 = Gauge32: 0
// PDU-MIB::externalSensorDecimalDigits.2 = Gauge32: 1
// PDU-MIB::externalSensorLowerCriticalThreshold.1 = INTEGER: 3
// PDU-MIB::externalSensorLowerCriticalThreshold.2 = INTEGER: 180
// PDU-MIB::externalSensorLowerWarningThreshold.1 = INTEGER: 7
// PDU-MIB::externalSensorLowerWarningThreshold.2 = INTEGER: 200
// PDU-MIB::externalSensorUpperCriticalThreshold.1 = INTEGER: 90
// PDU-MIB::externalSensorUpperCriticalThreshold.2 = INTEGER: 350
// PDU-MIB::externalSensorUpperWarningThreshold.1 = INTEGER: 85
// PDU-MIB::externalSensorUpperWarningThreshold.2 = INTEGER: 330
// PDU-MIB::externalSensorState.1 = INTEGER: normal(4)
// PDU-MIB::externalSensorState.2 = INTEGER: normal(4)
// PDU-MIB::externalSensorValue.1 = INTEGER: 0
// PDU-MIB::externalSensorValue.2 = INTEGER: 0

foreach ($oids as $index => $entry)
{
  $descr   = $entry['externalSensorName']; // The name set by the device's admin through Raritan's web interface.
  $oid     = ".1.3.6.1.4.1.13742.4.3.3.1.41.$index";
  $scale   = si_to_scale('units', $entry['externalSensorDecimalDigits']); // FIXME. other scale for externalSensorUnits = degreeF
  $limits  = array('limit_high'      => $entry['externalSensorUpperWarningThreshold']  * $scale,
                   'limit_low'       => $entry['externalSensorLowerCriticalThreshold'] * $scale,
                   'limit_high_warn' => $entry['externalSensorUpperCriticalThreshold'] * $scale,
                   'limit_low_warn'  => $entry['externalSensorLowerWarningThreshold']  * $scale);
  $value   = $entry['externalSensorValue'];
  $r_types = array(
    'rmsCurrent'     => 'current',
    //'peakCurrent', 'unbalancedCurrent',
    'rmsVoltage'     => 'voltage',
    'activePower'    => 'power',
    'apparentPower'  => 'apower',
    //'powerFactor', 'activeEnergy', 'apparentEnergy',
    'temperature'    => 'temperature',
    'humidity'       => 'humidity',
    'airFlow'        => 'airflow',
    //'airPressure', 'onOff', 'trip', 'vibration', 'waterDetection', 'smokeDetection', 'binary', 'contact', 'other', 'none'
  );

  if (isset($r_types[$entry['externalSensorType']]) && is_numeric($value))
  {
    discover_sensor($valid['sensor'], $r_types[$entry['externalSensorType']], $device, $oid, $index, 'raritan', $descr, $scale, $value, $limits);
  }
}

// EOF
