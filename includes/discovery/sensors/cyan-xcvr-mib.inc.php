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

echo 'CYAN-XCVR-MIB ';

/*
cyanXcvrTempHiAlrmThres.1.1.1 = 73000
cyanXcvrTempHiWarnThres.1.1.1 = 70000
cyanXcvrTempLoAlrmThres.1.1.1 = -8000
cyanXcvrTempLoWarnThres.1.1.1 = -5000
cyanXcvrTemperature.1.1.1 = 24496
cyanXcvrTxBiasCurrent.1.1.1 = 58
cyanXcvrTxBiasHiAlrmThres.1.1.1 = 109
cyanXcvrTxBiasHiWarnThres.1.1.1 = 94
cyanXcvrTxBiasLoAlrmThres.1.1.1 = 14
cyanXcvrTxBiasLoWarnThres.1.1.1 = 25
cyanXcvrTxPwrHiAlrmThres.1.1.1 = 2999
cyanXcvrTxPwrHiWarnThres.1.1.1 = 1999
cyanXcvrTxPwrLoAlrmThres.1.1.1 = -3000
cyanXcvrTxPwrLoWarnThres.1.1.1 = -2000
cyanXcvrVccVoltHiAlrmThres.1.1.1 = 3630
cyanXcvrVccVoltHiWarnThres.1.1.1 = 3464
cyanXcvrVccVoltLoAlrmThres.1.1.1 = 2970
cyanXcvrVccVoltLoWarnThres.1.1.1 = 3134
cyanXcvrVccVoltage.1.1.1 = 3324
*/

$oids = array ('cyanXcvrTempHiAlrmThres', 'cyanXcvrTempHiWarnThres', 'cyanXcvrTempLoAlrmThres', 'cyanXcvrTempLoWarnThres', 'cyanXcvrTemperature', 'cyanXcvrTxBiasCurrent', 'cyanXcvrTxBiasHiAlrmThres', 'cyanXcvrTxBiasHiWarnThres', 'cyanXcvrTxBiasLoAlrmThres', 'cyanXcvrTxBiasLoWarnThres', 'cyanXcvrVccVoltHiAlrmThres', 'cyanXcvrVccVoltHiWarnThres', 'cyanXcvrVccVoltLoAlrmThres', 'cyanXcvrVccVoltLoWarnThres', 'cyanXcvrVccVoltage', 'cyanXcvrTxPwrHiAlrmThres', 'cyanXcvrTxPwrHiWarnThres', 'cyanXcvrTxPwrLoAlrmThres', 'cyanXcvrTxPwrLoWarnThres');

$data = array();
foreach ($oids as $oid)
{
  $data = snmpwalk_cache_oid($device, $oid, $data, 'CYAN-XCVR-MIB:CYAN-GEPORT-MIB:CYAN-TENGPORT-MIB', mib_dirs('cyan'));
}

// Try to identify which IF-MIB port is being referred to, and populate the 'measured_entity' if we can.

$ifNames = snmpwalk_cache_oid($device, 'ifName', array(), 'IF-MIB');
foreach ($ifNames as $ifIndex => $entry)
{

  list(, $cyan_index) = explode("-", $entry['ifName'], 2);
  $cyan_index = str_replace("-", ".", $cyan_index);

  if (is_array($data[$cyan_index]))
  {
    $port = get_port_by_ifIndex($device['device_id'], $ifIndex);
    $data[$cyan_index]['measured_entity'] = $port['port_id'];
  }

}

foreach ($data as $index => $entry)
{

  $descr = "Transceiver " . str_replace(".", "-", $index);

  $options = array();
  if (isset($entry['measured_entity'])) { $options['measured_entity'] = $entry['measured_entity']; $options['measured_class'] = 'port'; }
  $options['limit_high'] = $entry['cyanXcvrTxBiasHiAlrmThres'] * 0.001;
  $options['limit_low']  = $entry['cyanXcvrTxBiasLoAlrmThres'] * 0.001;
  $options['warn_high']  = $entry['cyanXcvrTxBiasHiWarnThres']  * 0.001;
  $options['warn_low']   = $entry['cyanXcvrTxBiasHiWarnThres']  * 0.001;

  discover_sensor($valid['sensor'], 'current', $device, ".1.3.6.1.4.1.28533.5.30.140.1.1.1.43." . $index, $index, 'cyanXcvrTxBiasCurrent', $descr. " TX Bias", 0.001, $entry['cyanXcvrTxBiasCurrent'], $options);

  $options = array();
  if (isset($entry['measured_entity'])) { $options['measured_entity'] = $entry['measured_entity']; $options['measured_class'] = 'port'; }
  $options['limit_high'] = $entry['cyanXcvrTempHiAlrmThres'] * 0.001;
  $options['limit_low']  = $entry['cyanXcvrTempLoAlrmThres'] * 0.001;
  $options['warn_high']  = $entry['cyanXcvrTempHiWarnThres']  * 0.001;
  $options['warn_low']   = $entry['cyanXcvrTempHiWarnThres']  * 0.001;

  discover_sensor($valid['sensor'], 'temperature', $device, ".1.3.6.1.4.1.28533.5.30.140.1.1.1.42." . $index, $index, 'cyanXcvrTemperature', $descr, 0.001, $entry['cyanXcvrTemperature'], $options);

  $options = array();
  if (isset($entry['measured_entity'])) { $options['measured_entity'] = $entry['measured_entity']; $options['measured_class'] = 'port'; }
  $options['limit_high'] = $entry['cyanXcvrVccVoltHiAlrmThres'] * 0.001;
  $options['limit_low']  = $entry['cyanXcvrVccVoltLoAlrmThres'] * 0.001;
  $options['warn_high']  = $entry['cyanXcvrVccVoltHiWarnThres']  * 0.001;
  $options['warn_low']   = $entry['cyanXcvrVccVoltHiWarnThres']  * 0.001;

  discover_sensor($valid['sensor'], 'voltage', $device, ".1.3.6.1.4.1.28533.5.30.140.1.1.1.56." . $index, $index, 'cyanXcvrVccVoltage', $descr . " Vcc", 0.001, $entry['cyanXcvrVccVoltage'], $options);

}

// EOF
