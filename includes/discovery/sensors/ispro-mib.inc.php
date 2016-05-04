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

echo(" ISPRO-MIB ");

$oids = snmpwalk_cache_oid($device, "isDeviceConfigTable", array(), "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceMonitorTemperatureTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceConfigTemperatureTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceMonitorHumidityTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceConfigHumidityTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceMonitorDigitalInTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceConfigDigitalInTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));

// isConfigTemperatureUnit.0 = INTEGER: celsius(1)
$isConfigTemperatureUnit = snmp_get($device, "isConfigTemperatureUnit.0", "-Oqv", "ISPRO-MIB", mib_dirs('jacarta'));

foreach ($oids as $index => $entry)
{
  // Skip if this sensor has been disabled for display in the web interface
  if ($entry['isDeviceConfigDisplay'] == 'enabled')
  {
    // Temperature

    // isDeviceConfigTemperatureIndex.1 = INTEGER: 1
    // isDeviceMonitorTemperatureName.1 = STRING: "Temperature1"
    // isDeviceMonitorTemperature.1 = INTEGER: 3121
    // isDeviceConfigTemperatureName.1 = STRING: "Temperature1"

    $descr   = $entry['isDeviceMonitorTemperatureName'];
    $oid     = ".1.3.6.1.4.1.19011.1.3.2.1.3.1.1.1.3.$index";
    $value   = $entry['isDeviceMonitorTemperature'];

    // Warning/Critical limits can be enabled/disabled in the web interface. Use them as supplied if enabled, calculate our own if not enabled.
    //
    // isDeviceConfigTemperatureLowWarning.1 = INTEGER: 2300
    // isDeviceConfigTemperatureLowCritical.1 = INTEGER: 2000
    // isDeviceConfigTemperatureHighWarning.1 = INTEGER: 2700
    // isDeviceConfigTemperatureHighCritical.1 = INTEGER: 3000
    // isDeviceConfigTemperatureLowWarningState.1 = INTEGER: disabled(2)
    // isDeviceConfigTemperatureLowCriticalState.1 = INTEGER: disabled(2)
    // isDeviceConfigTemperatureHighWarningState.1 = INTEGER: disabled(2)
    // isDeviceConfigTemperatureHighCriticalState.1 = INTEGER: disabled(2)

    $options = array('limit_high'      => ($entry['isDeviceConfigTemperatureHighCriticalState'] == 'enabled' ? $entry['isDeviceConfigTemperatureHighCritical'] / 100 : NULL),
                    'limit_high_warn' => ($entry['isDeviceConfigTemperatureHighWarningState']  == 'enabled' ? $entry['isDeviceConfigTemperatureHighWarning']  / 100 : NULL),
                    'limit_low_warn'  => ($entry['isDeviceConfigTemperatureLowWarningState']   == 'enabled' ? $entry['isDeviceConfigTemperatureLowWarning']   / 100 : NULL),
                    'limit_low'       => ($entry['isDeviceConfigTemperatureLowCriticalState']  == 'enabled' ? $entry['isDeviceConfigTemperatureLowCritical']  / 100 : NULL));

    if ($isConfigTemperatureUnit == 'fahrenheit')
    {
      $options['sensor_unit'] = 'F';

      foreach (array('limit_high', 'limit_low', 'limit_high_warn', 'limit_low_warn') as $param)
      {
        $options[$param] = f2c($options[$param]); // Convert limits from fahrenheit to celsius
      }
    } else {
      $options['sensor_unit'] = 'C';
    }

    // Not used:
    // isDeviceConfigTemperatureCalibration.1 = INTEGER: temperatureIncrease0Point0(1)
    // isDeviceConfigTemperatureHysteresis.1 = INTEGER: 200

    // 32768 = No sensor connected
    if ($value != 32768 && $value != '')
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, "isDeviceMonitorTemperature.$index", 'ispro-mib', $descr, 0.01, $value, $options);
    }

    $oid     = ".1.3.6.1.4.1.19011.1.3.2.1.3.1.1.1.4.$index";
    $value   = $entry['isDeviceMonitorTemperatureAlarm'];

    // isDeviceMonitorTemperatureAlarm.1 = INTEGER: normal(3)

    // unknown = No sensor connected
    if ($value != 'unknown' && $value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, "isDeviceMonitorTemperatureAlarm.$index", 'ispro-mib-threshold-state', $descr, 1, $value);
    }

    // Humidity

    // isDeviceConfigHumidityIndex.1 = INTEGER: 1
    // isDeviceMonitorHumidityName.1 = STRING: "Humidity1"
    // isDeviceMonitorHumidity.1 = INTEGER: 2166
    // isDeviceConfigHumidityName.1 = STRING: "Humidity1"

    $descr   = $entry['isDeviceMonitorHumidityName'];
    $oid     = "1.3.6.1.4.1.19011.1.3.2.1.3.1.2.1.3.$index";
    $value   = $entry['isDeviceMonitorHumidity'];

    // Warning/Critical limits can be enabled/disabled in the web interface. Use them as supplied if enabled, calculate our own if not enabled.
    //
    // isDeviceConfigHumidityLowWarning.1 = INTEGER: 4000
    // isDeviceConfigHumidityLowCritical.1 = INTEGER: 3500
    // isDeviceConfigHumidityHighWarning.1 = INTEGER: 5000
    // isDeviceConfigHumidityHighCritical.1 = INTEGER: 5500
    // isDeviceConfigHumidityLowWarningState.1 = INTEGER: disabled(2)
    // isDeviceConfigHumidityLowCriticalState.1 = INTEGER: disabled(2)
    // isDeviceConfigHumidityHighWarningState.1 = INTEGER: disabled(2)
    // isDeviceConfigHumidityHighCriticalState.1 = INTEGER: disabled(2)

    $options = array('limit_high'      => ($entry['isDeviceConfigHumidityHighCriticalState'] == 'enabled' ? $entry['isDeviceConfigHumidityHighCritical'] / 100 : NULL),
                    'limit_high_warn' => ($entry['isDeviceConfigHumidityHighWarningState']  == 'enabled' ? $entry['isDeviceConfigHumidityHighWarning']  / 100 : NULL),
                    'limit_low_warn'  => ($entry['isDeviceConfigHumidityLowWarningState']   == 'enabled' ? $entry['isDeviceConfigHumidityLowWarning']   / 100 : NULL),
                    'limit_low'       => ($entry['isDeviceConfigHumidityLowCriticalState']  == 'enabled' ? $entry['isDeviceConfigHumidityLowCritical']  / 100 : NULL));

    // Not used:
    // isDeviceConfigHumidityCalibration.1 = INTEGER: humidityIncrease0Point0(1)
    // isDeviceConfigHumidityHysteresis.2 = INTEGER: 500

    // 32768 = No sensor connected
    if ($value != 32768 && $value != '')
    {
      discover_sensor($valid['sensor'], 'humidity', $device, $oid, "isDeviceMonitorHumidity.$index", 'ispro-mib', $descr, 0.01, $value, $options);
    }

    $oid     = ".1.3.6.1.4.1.19011.1.3.2.1.3.1.2.1.4.$index";
    $value   = $entry['isDeviceMonitorHumidityAlarm'];

    // isDeviceMonitorHumidityAlarm.1 = INTEGER: normal(3)

    // unknown = No sensor connected
    if ($value != 'unknown' && $value != '')
    {
      discover_sensor($valid['sensor'], 'state', $device, $oid, "isDeviceMonitorHumidityAlarm.$index", 'ispro-mib-threshold-state', $descr, 1, $value);
    }
  }
}

$oids = snmpwalk_cache_oid($device, "isDeviceMonitorDigitalInTable", array(), "ISPRO-MIB", mib_dirs('jacarta'));
$oids = snmpwalk_cache_oid($device, "isDeviceConfigDigitalInTable", $oids, "ISPRO-MIB", mib_dirs('jacarta'));

foreach ($oids as $index => $entry)
{
  // Unfortunately, there is no (direct) SNMP link between the sensors connected above, even though they are represented together in the Web UI.
  // Unless we use a ugly "divide by 2" hack, we can't know if these alerts are meant to be "not displayed" like we do for Temp/Humidity.
  // If you don't want digital sensors displayed, make sure they are set to Disabled and not Normal Open or Normal Close.

  // isDeviceMonitorDigitalInIndex.1 = INTEGER: 1
  // isDeviceMonitorDigitalInName.1 = STRING: "Alarm1-1"
  // isDeviceMonitorDigitalIn.1 = INTEGER: inactive(2)
  // isDeviceMonitorDigitalInAlarm.1 = INTEGER: normal(1)

  $descr   = $entry['isDeviceMonitorDigitalInName'] /* . ' (' . $entry['isDeviceConfigDigitalInState'] . ')'*/;
  $oid     = "1.3.6.1.4.1.19011.1.3.2.1.3.1.3.1.4.$index";
  $value   = $entry['isDeviceMonitorDigitalInAlarm'];

  if ($entry['isDeviceConfigDigitalInState'] != 'disabled' && $value != '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "isDeviceMonitorDigitalInAlarm.$index", 'ispro-mib-trigger-state', $descr, 1, $value);
  }
}

// EOF
