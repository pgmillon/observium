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

echo 'CYAN-CEM-MIB ';

/*

CYAN-CEM-MIB::cyanCemCurrent.1.32 = INTEGER: 187
CYAN-CEM-MIB::cyanCemCurrent.1.33 = INTEGER: 187
CYAN-CEM-MIB::cyanCemDescription.1.32 = STRING: CEMi
CYAN-CEM-MIB::cyanCemDescription.1.33 = STRING: CEMi
CYAN-CEM-MIB::cyanCemExhaustAirTemp.1.32 = INTEGER: 29125
CYAN-CEM-MIB::cyanCemExhaustAirTemp.1.33 = INTEGER: 24875
CYAN-CEM-MIB::cyanCemExhaustTempAlarmHighThres.1.32 = INTEGER: 80000
CYAN-CEM-MIB::cyanCemExhaustTempAlarmHighThres.1.33 = INTEGER: 80000
CYAN-CEM-MIB::cyanCemExhaustTempAlarmLowThres.1.32 = INTEGER: -45000
CYAN-CEM-MIB::cyanCemExhaustTempAlarmLowThres.1.33 = INTEGER: -45000
CYAN-CEM-MIB::cyanCemExhaustTempWarnHighThres.1.32 = INTEGER: 75000
CYAN-CEM-MIB::cyanCemExhaustTempWarnHighThres.1.33 = INTEGER: 75000
CYAN-CEM-MIB::cyanCemExhaustTempWarnLowThres.1.32 = INTEGER: -40000
CYAN-CEM-MIB::cyanCemExhaustTempWarnLowThres.1.33 = INTEGER: -40000
CYAN-CEM-MIB::cyanCemExpectedTemperatureRise.1.32 = INTEGER: 8000
CYAN-CEM-MIB::cyanCemExpectedTemperatureRise.1.33 = INTEGER: 8000
CYAN-CEM-MIB::cyanCemIdentifier.1.32 = STRING: 1-32
CYAN-CEM-MIB::cyanCemIdentifier.1.33 = STRING: 1-33
CYAN-CEM-MIB::cyanCemIntakeAirTemp.1.32 = INTEGER: 23875
CYAN-CEM-MIB::cyanCemIntakeAirTemp.1.33 = INTEGER: 20000
CYAN-CEM-MIB::cyanCemIntakeTempAlarmHighThres.1.32 = INTEGER: 80000
CYAN-CEM-MIB::cyanCemIntakeTempAlarmHighThres.1.33 = INTEGER: 80000
CYAN-CEM-MIB::cyanCemIntakeTempAlarmLowThres.1.32 = INTEGER: -45000
CYAN-CEM-MIB::cyanCemIntakeTempAlarmLowThres.1.33 = INTEGER: -45000
CYAN-CEM-MIB::cyanCemIntakeTempWarnHighThres.1.32 = INTEGER: 75000
CYAN-CEM-MIB::cyanCemIntakeTempWarnHighThres.1.33 = INTEGER: 75000
CYAN-CEM-MIB::cyanCemIntakeTempWarnLowThres.1.32 = INTEGER: -40000
CYAN-CEM-MIB::cyanCemIntakeTempWarnLowThres.1.33 = INTEGER: -40000

*/

$entries = snmpwalk_cache_oid($device, 'cyanCemTable', array(), 'CYAN-CEM-MIB', mib_dirs('cyan'));

foreach ($entries as $index => $entry)
{

  $descr = $entry['cyanCemDescription'] . ' ' . $entry['cyanCemIdentifier'];

  discover_sensor($valid['sensor'], 'current', $device, ".1.3.6.1.4.1.28533.5.30.50.1.1.1.11." . $index, $index, 'cyanCemCurrent', $descr, 0.01, $entry['cyanCemCurrent']);

  $options = array();
  $options['limit_high'] = $entry['cyanCemIntakeTempAlarmHighThres'] * 0.001;
  $options['limit_low']  = $entry['cyanCemIntakeTempAlarmLowThres'] * 0.001;
  $options['warn_high']  = $entry['cyanCemIntakeTempWarnHighThres']  * 0.001;
  $options['warn_low']   = $entry['cyanCemIntakeTempWarnLowThres']  * 0.001;

  discover_sensor($valid['sensor'], 'temperature', $device, ".1.3.6.1.4.1.28533.5.30.50.1.1.1.20." . $index, $index, 'cyanCemIntakeAirTemp', $descr. ' Intake', 0.001, $entry['cyanCemIntakeAirTemp'], $options);

  $options = array();
  $options['limit_high'] = $entry['cyanCemExhaustTempAlarmHighThres'] * 0.001;
  $options['limit_low']  = $entry['cyanCemExhaustTempAlarmLowThres'] * 0.001;
  $options['warn_high']  = $entry['cyanCemExhaustTempWarnHighThres']  * 0.001;
  $options['warn_low']   = $entry['cyanCemExhaustTempWarnLowThres']  * 0.001;

  discover_sensor($valid['sensor'], 'temperature', $device, ".1.3.6.1.4.1.28533.5.30.50.1.1.1.13." . $index, $index, 'cyanCemExhaustAirTemp', $descr. ' Exhaust', 0.001, $entry['cyanCemExhaustAirTemp'], $options);

  $options = array();
  $options['limit_high'] = $entry['cyanCemOvervoltageThreshold']  * 0.001;
  $options['limit_low']  = $entry['cyanCemUndervoltageThreshold'] * 0.001;

  discover_sensor($valid['sensor'], 'voltage', $device, ".1.3.6.1.4.1.28533.5.30.50.1.1.1.43." . $index, $index, 'cyanCemPwrFeedAVoltage', $descr.' Feed A', 0.001, $entry['cyanCemPwrFeedAVoltage'], $options);
  discover_sensor($valid['sensor'], 'voltage', $device, ".1.3.6.1.4.1.28533.5.30.50.1.1.1.45." . $index, $index, 'cyanCemPwrFeedBVoltage', $descr.' Feed B', 0.001, $entry['cyanCemPwrFeedBVoltage'], $options);

}

// EOF
