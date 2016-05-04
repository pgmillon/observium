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

// Currently unused fields:
//
// mRackName.1.0 = STRING: DPDU2B
// mRackPosition.1.0 = STRING: USDC1.2
// pFirmwareVersion.1.0 = STRING: 3.1.0.8
// pHardwareVersion.1.0 = STRING: 1.0.0.0
// pFirmwareID.1.0 = STRING: RTF0038
// pHardwareID.1.0 = STRING: RTH0050

echo(" ES-RACKTIVITY-MIB ");

$oids = snmpwalk_cache_twopart_oid($device, "eMasterTable", array(), 'ES-RACKTIVITY-MIB', mib_dirs('racktivity'));

foreach ($oids as $modIndex => $module_entry)
{
  foreach ($module_entry as $index => $entry)
  {
    $descr = $entry['mModuleName'];

    // mTemperature.1.0 = Gauge32: 310.2 K
    // mMinTemperatureWarning.1.0 = Gauge32: 273.2 K
    // mMaxTemperatureWarning.1.0 = Gauge32: 333.2 K

    $value   = $entry['mTemperature'];
    $scale   = 0.1;
    $oid     = ".1.3.6.1.4.1.34097.9.77.1.1.11.$modIndex.$index";

    $options = array('limit_high'      => (isset($entry['mMaxTemperatureWarning']) ? $entry['mMaxTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                     'limit_low'       => (isset($entry['mMinTemperatureWarning']) ? $entry['mMinTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                     'sensor_unit'     => 'K',
                    );

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "mTemperature.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale, $options);
    }
  }
}

$oids = snmpwalk_cache_twopart_oid($device, "ePowerTable", array(), 'ES-RACKTIVITY-MIB', mib_dirs('racktivity'));

// mTemperature.1.0 = Gauge32: 310.2 K
// mMinTemperatureWarning.1.0 = Gauge32: 273.2 K
// mMaxTemperatureWarning.1.0 = Gauge32: 333.2 K

$value   = $entry['mTemperature'];
$scale   = 0.01;
$oid     = ".1.3.6.1.4.1.34097.9.77.1.1.11.$modIndex.$index";

$options = array('limit_high'      => (isset($entry['mMaxTemperatureWarning']) ? $entry['mMaxTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                 'limit_low'       => (isset($entry['mMinTemperatureWarning']) ? $entry['mMinTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                 'sensor_unit'     => 'K',
                );

if ($value != 0)
{
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, "mTemperature.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale, $options);
}

$oids = snmpwalk_cache_twopart_oid($device, "ePowerTable", array(), 'ES-RACKTIVITY-MIB', mib_dirs('racktivity'));

if (OBS_DEBUG > 1) { print_vars($oids); }

// mGeneralModuleStatus.1.0 = Gauge32: 0
// mSpecificModuleStatus.1.0 = Gauge32: 255
// pGeneralModuleStatus.1.0 = Gauge32: 0
// pSpecificModuleStatus.1.0 = Gauge32: 0
// Values not documented, no no way to make this into a state sensor.

// mCloudStatus.1.0 = Gauge32: 1
// FIXME - TODO
// 0 = Idle state; 1 = Disabled; 2 = Initialising; 3 = Initialising - No connection; 4 = Initialising - No key; 5 = Connection Ok; 6 = Connection Failed; 7 = Connection Failed - No key.

foreach ($oids as $modIndex => $module_entry)
{

  foreach ($module_entry as $index => $entry)
  {
    // pExternalSensorLabel.1.0 = STRING: CurrentSensor1
    $descr = $entry['pExternalSensorLabel'];

    // pVoltage.1.0 = Gauge32: 231.04 V
    // pMaxVoltageWarning.1.0 = Gauge32: 270.00 V
    // pMinVoltageWarning.1.0 = Gauge32: 60.00 V
    $value   = $entry['pVoltage'];
    $scale   = 0.01;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.4.$modIndex.$index";

    $options = array('limit_high'      => (isset($entry['pMaxVoltageWarning']) ? $entry['pMaxVoltageWarning'] : NULL),
                     'limit_low'       => (isset($entry['pMinVoltageWarning']) ? $entry['pMinVoltageWarning'] : NULL),
                    );

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'voltage', $device, $oid, "pVoltage.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale, $options);
    }

    // pTemperature.1.0 = Gauge32: 293.2 K
    // pMinTemperatureWarning.1.0 = Gauge32: 273.2 K
    // pMaxTemperatureWarning.1.0 = Gauge32: 333.2 K
    $value   = $entry['pTemperature'];
    $scale   = 0.1;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.11.$modIndex.$index";

    $options = array('limit_high'      => (isset($entry['pMaxTemperatureWarning']) ? $entry['pMaxTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                     'limit_low'       => (isset($entry['pMinTemperatureWarning']) ? $entry['pMinTemperatureWarning'] - 273.15 : NULL), // Convert Kelvin limit to Celsius
                     'sensor_unit'     => 'K',
                    );

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "pTemperature.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale, $options);
    }

    // pBigCurrent.1.0 = Gauge32: 30370 A
    // pMinBigCurrentWarning.1.0 = Gauge32: 0 A
    // pMaxBigCurrentWarning.1.0 = Gauge32: 400000 A
    $value   = $entry['pBigCurrent'];
    $scale   = 0.0001;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.52.$modIndex.$index";

    $options = array('limit_high'      => (isset($entry['pMaxBigCurrentWarning']) ? $entry['pMaxBigCurrentWarning'] * $scale : NULL),
                     'limit_low'       => (isset($entry['pMinBigCurrentWarning']) ? $entry['pMinBigCurrentWarning'] * $scale : NULL),
                    );

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'current', $device, $oid, "pBigCurrent.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value, $options);
    }

    // pBigPower.1.0 = Gauge32: 479.410 W
    // pMaxBigPowerWarning.1.0 = Gauge32: 10000.000 W
    $value   = $entry['pBigPower'];
    $scale   = 0.001;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.53.$modIndex.$index";

    $options = array('limit_high'      => (isset($entry['pMaxBigPowerWarning']) ? $entry['pMaxBigPowerWarning'] : NULL),
                     'limit_low'       => (isset($entry['pMinBigPowerWarning']) ? $entry['pMinBigPowerWarning'] : NULL),
                    );

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'power', $device, $oid, "pBigPower.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale, $options);
    }

    // pActiveEnergy.1.0 = Gauge32: 181.180 kWh
    // FUTUREME - Currently no kWh counters in Observium

    // pApparentEnergy.1.0 = Gauge32: 748.406 kVAh
    // FUTUREME - Currently no kVAh counters in Observium

    // pFrequency.1.0 = Gauge32: 50.001 Hz
    $value   = $entry['pFrequency'];
    $scale   = 0.001;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.5.$modIndex.$index";

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'frequency', $device, $oid, "pFrequency.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale);
    }

    // pPowerFactor.1.0 = Gauge32: 68 %
    // FUTUREME - Currently no power factor in Observium

    // pTotalHarmonicDistortion.1.0 = Gauge32: 100.0 %
    // FUTUREME - Currently no harmonic distortion in Observium

    // pBigApparentPower.1.0 = Gauge32: 703.225 VA
    $value   = $entry['pBigApparentPower'];
    $scale   = 0.001;
    $oid     = ".1.3.6.1.4.1.34097.9.80.1.1.54.$modIndex.$index";

    if ($value != 0)
    {
      discover_sensor($valid['sensor'], 'apower', $device, $oid, "pBigApparentPower.$modIndex.$index", 'es-racktivity-mib', $descr, $scale, $value / $scale);
    }
  }
}

// EOF
