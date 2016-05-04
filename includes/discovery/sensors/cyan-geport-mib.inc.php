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

echo 'CYAN-GEPORT-MIB ';

/*
cyanXcvrTxPwrHiAlrmThres.1.1.1 = 2999
cyanXcvrTxPwrHiWarnThres.1.1.1 = 1999
cyanXcvrTxPwrLoAlrmThres.1.1.1 = -3000
cyanXcvrTxPwrLoWarnThres.1.1.1 = -2000
*/

$oids = array ('cyanXcvrTxPwrHiAlrmThres', 'cyanXcvrTxPwrHiWarnThres', 'cyanXcvrTxPwrLoAlrmThres', 'cyanXcvrTxPwrLoWarnThres');

$thresholds = array();
foreach ($oids as $oid)
{
  $thresholds = snmpwalk_cache_oid($device, $oid, $thresholds, 'CYAN-XCVR-MIB:CYAN-GEPORT-MIB:CYAN-TENGPORT-MIB', mib_dirs('cyan'));
}

/*
cyanGEPortAdminState.1.1.24.1 = adminunlocked
cyanGEPortAutoinserviceSoakTimeSec.1.1.24.1 = 28800
cyanGEPortDescription.1.1.24.1 = GE_Fiber_Port
cyanGEPortExternalFiberMultishelfLink.1.1.24.1 = disabled
cyanGEPortExternalFiberRemotePort.1.1.24.1 =
cyanGEPortLoopbackControl.1.1.24.1 = disabled
cyanGEPortOperState.1.1.24.1 = is
cyanGEPortOperStateQual.1.1.24.1 = nr
cyanGEPortRxPwr.1.1.24.1 = -7736
cyanGEPortSecServState.1.1.24.1 = "80 "
cyanGEPortTransmitControl.1.1.24.1 = on
cyanGEPortTxPwr.1.1.24.1 = -5224
cyanGEPortTxStatus.1.1.24.1 = on
*/

$data = array();
$oids = array('cyanGEPortRxPwr', 'cyanGEPortTxPwr');
foreach ($oids as $oid)
{
  $data = snmpwalk_cache_oid($device, $oid, $data, 'CYAN-GEPORT-MIB', mib_dirs('cyan'));
}

// Try to identify which IF-MIB port is being referred to, and populate the 'measured_entity' if we can.

$ifNames = snmpwalk_cache_oid($device, 'ifName', array(), 'IF-MIB');
foreach ($ifNames as $ifIndex => $entry)
{

  list(, $cyan_index) = explode("-", $entry['ifName'], 2);

  if ($port = get_port_by_ifIndex($device['device_id'], $ifIndex))
  {
    $port_translates[$cyan_index]['measured_entity'] = $port['port_id'];
  }
}

foreach ($data as $index => $entry)
{

  list($shelf_id, $mod_id, $xcvr_id, $port_id) = explode(".", $index);

  $descr = "Transceiver ".$shelf_id."-".$mod_id."-".$xcvr_id." (".$port_id.")";
  $xcvr_string = $shelf_id."-".$mod_id."-".$xcvr_id;

  $options = array();
  if (isset($port_translates[$xcvr_string]['measured_entity'])) { $options['measured_entity'] = $port_translates[$xcvr_string]['measured_entity']; $options['measured_class'] = 'port'; }

//  $options['limit_high'] = $entry['cyanXcvrTxBiasHiAlrmThres'] * 0.001;
//  $options['limit_low']  = $entry['cyanXcvrTxBiasLoAlrmThres'] * 0.001;
//  $options['warn_high']  = $entry['cyanXcvrTxBiasHiWarnThres']  * 0.001;
//  $options['warn_low']   = $entry['cyanXcvrTxBiasHiWarnThres']  * 0.001;

  discover_sensor($valid['sensor'], 'dbm', $device, ".1.3.6.1.4.1.28533.5.30.160.1.1.1.16." . $index, $index, 'cyanGEPortTxPwr', $descr. " RX Power", 0.001, $entry['cyanGEPortTxPwr'], $options);

  $options = array();
  if (isset($port_translates[$xcvr_string]['measured_entity'])) { $options['measured_entity'] = $port_translates[$xcvr_string]['measured_entity']; $options['measured_class'] = 'port'; }

//  $options['limit_high'] = $entry['cyanXcvrVccVoltHiAlrmThres'] * 0.001;
//  $options['limit_low']  = $entry['cyanXcvrVccVoltLoAlrmThres'] * 0.001;
//  $options['warn_high']  = $entry['cyanXcvrVccVoltHiWarnThres']  * 0.001;
//  $options['warn_low']   = $entry['cyanXcvrVccVoltHiWarnThres']  * 0.001;

  discover_sensor($valid['sensor'], 'dbm', $device, ".1.3.6.1.4.1.28533.5.30.160.1.1.1.13." . $index, $index, 'cyanGEPortRxPwr', $descr . " TX Power", 0.001, $entry['cyanGEPortRxPwr'], $options);

}

unset($port_translates, $thresholds, $oids, $data, $entry, $index);

// EOF
