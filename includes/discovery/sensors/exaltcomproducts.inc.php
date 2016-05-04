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

echo(" ExaltComProducts ");

//ExaltComProducts::locLinkState.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::locTempAlarm.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::locCurrentTemp.0 = INTEGER: 33 C
//ExaltComProducts::locCurrentTempS.0 = STRING: 33 deg C.
//ExaltComProducts::remLinkState.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::remTempAlarm.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::remCurrentTemp.0 = INTEGER: 29 C
//ExaltComProducts::remCurrentTempS.0 = STRING: 29 dec C.
$discover['temp'] = snmp_get_multi($device, 'locCurrentTemp.0 remCurrentTemp.0', '-OQUs', 'ExaltComProducts', mib_dirs('exalt'));

if (is_numeric($discover['temp'][0]['locCurrentTemp']) && $discover['temp'][0]['locCurrentTemp'] > 0)
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.3.0';
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'locCurrentTemp.0', 'exaltcomproducts', "Temperature (Internal)", 1, $discover['temp'][0]['locCurrentTemp']);
}
if (is_numeric($discover['temp'][0]['remCurrentTemp']) && $discover['temp'][0]['remCurrentTemp'] > 0)
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.3.0';
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'remCurrentTemp.0', 'exaltcomproducts', "Temperature (Far end radio)", 1, $discover['temp'][0]['remCurrentTemp']);
}

//ExaltComProducts::locCurrentRSL.0 = INTEGER: -65 dBm
//ExaltComProducts::locCurrentRSLstr.0 = STRING: -65 (dBm).
//ExaltComProducts::locMinRSL.0 = INTEGER: -80 dBm
//ExaltComProducts::locMinRSLstr.0 = STRING: -80 (dBm).
//ExaltComProducts::locMaxRSL.0 = INTEGER: -61 dBm
//ExaltComProducts::locMaxRSLstr.0 = STRING: -61 (dBm). dBm
//ExaltComProducts::remCurrentRSL.0 = INTEGER: -66 dBm
//ExaltComProducts::remCurrentRSLstr.0 = STRING: -66 (dBm).
//ExaltComProducts::remMinRSL.0 = INTEGER: -82 dBm
//ExaltComProducts::remMinRSLstr.0 = STRING: -82 (dBm).
//ExaltComProducts::remMaxRSL.0 = INTEGER: -62 dBm
//ExaltComProducts::remMaxRSLstr.0 = STRING: -62 (dBm). dBm
$discover['dbm'] = snmp_get_multi($device, 'locCurrentRSL.0 locMinRSL.0 locMaxRSL.0 remCurrentRSL.0 remMinRSL.0 remMaxRSL.0', '-OQUs', 'ExaltComProducts', mib_dirs('exalt'));

if (is_numeric($discover['dbm'][0]['locCurrentRSL']))
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.1.3.0';
  $limits = array('limit_high' => $discover['dbm'][0]['locMaxRSL'],
                  'limit_low'  => $discover['dbm'][0]['locMinRSL']);
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'locCurrentRSL.0', 'exaltcomproducts', "Received Signal Level (Internal)", 1, $discover['dbm'][0]['locCurrentRSL'], $limits);
}
if (is_numeric($discover['dbm'][0]['remCurrentRSL']))
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.2.3.0';
  $limits = array('limit_high' => $discover['dbm'][0]['remMaxRSL'],
                  'limit_low'  => $discover['dbm'][0]['remMinRSL']);
  discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'remCurrentRSL.0', 'exaltcomproducts', "Received Signal Level (Far end radio)", 1, $discover['dbm'][0]['remCurrentRSL'], $limits);
}

//ExaltComProducts::locLinkState.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::locErrorDuration.0 = INTEGER: 30 Seconds
//ExaltComProducts::locErrorDurationStr.0 = STRING: 30 seconds.
//ExaltComProducts::locUnavailDuration.0 = INTEGER: 0 Seconds
//ExaltComProducts::locUnavailDurationStr.0 = STRING: 0 seconds.
//ExaltComProducts::remLinkState.0 = INTEGER: almNORMAL(0)
//ExaltComProducts::remErrorDuration.0 = INTEGER: 3 Seconds
//ExaltComProducts::remErrorDurationStr.0 = STRING: 3 seconds.
//ExaltComProducts::remUnavailDuration.0 = INTEGER: 0 Seconds
//ExaltComProducts::remUnavailDurationStr.0 = STRING: 0 seconds.
$discover['state'] = snmp_get_multi($device, 'locLinkState.0 locErrorDuration.0 locUnavailDuration.0 remLinkState.0 remErrorDuration.0 remUnavailDuration.0', '-OQUs', 'ExaltComProducts', mib_dirs('exalt'));

$sensor_state_type = 'exaltcomproducts-state';
$options           = array('entPhysicalClass' => 'linkstate');
if (!empty($discover['state'][0]['locLinkState']))
{
  $oid   = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.1.0';
  $value = $discover['state'][0]['locLinkState'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'locLinkState.0', $sensor_state_type, "Link Status (Internal)", NULL, $value, $options);
}
if (!empty($discover['state'][0]['remLinkState']))
{
  $oid   = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.1.0';
  $value = $discover['state'][0]['remLinkState'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'remLinkState.0', $sensor_state_type, "Link Status (Far end radio)", NULL, $value, $options);
}
if (is_numeric($discover['state'][0]['locErrorDuration']) && is_numeric($discover['state'][0]['locUnavailDuration']))
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.1.5.0';
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'locErrorDuration.0', 'exaltcomproducts', "Errored Seconds (Internal)", 1, $discover['state'][0]['locErrorDuration']);
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.1.7.0';
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'locUnavailDuration.0', 'exaltcomproducts', "Unavailable Seconds (Internal)", 1, $discover['state'][0]['locUnavailDuration']);
}
if (is_numeric($discover['state'][0]['remErrorDuration']) && is_numeric($discover['state'][0]['remUnavailDuration']))
{
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.2.5.0';
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'remErrorDuration.0', 'exaltcomproducts', "Errored Seconds (Far end radio)", 1, $discover['state'][0]['remErrorDuration']);
  $oid = '.1.3.6.1.4.1.25651.1.2.4.3.2.7.0';
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'remUnavailDuration.0', 'exaltcomproducts', "Unavailable Seconds (Far end radio)", 1, $discover['state'][0]['remUnavailDuration']);
}

unset($discover, $oid, $value, $sensor_state_type);

// EOF
