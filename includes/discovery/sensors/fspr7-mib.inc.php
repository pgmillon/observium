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

$mib = "FspR7-MIB";
echo(" $mib ");

$oids = snmpwalk_cache_oid($device, "currentPmSnapshotOutputPower",     array(), $mib);
$oids = snmpwalk_cache_oid($device, "currentPmSnapshotInputPower",        $oids, $mib);
$oids = snmpwalk_cache_oid($device, "currentPmSnapshotRxLineAttenuation", $oids, $mib);
$oids = snmpwalk_cache_oid($device, "currentPmSnapshotTxLineAttenuation", $oids, $mib);

$scale = 0.1;
foreach ($oids as $index => $entry)
{
  $ifDescr = snmp_get($device, "ifDescr.$index", "-Oqv");
  $options = array('entPhysicalIndex' => $index);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  // Output Power
  //FspR7-MIB::currentPmSnapshotOutputPower.269092419 = -990
  //FspR7-MIB::currentPmSnapshotOutputPower.269092609 = -22
  //FspR7-MIB::currentPmSnapshotOutputPower.269092673 = 49
  //FspR7-MIB::currentPmSnapshotOutputPower.269092865 = -19
  if (is_numeric($entry['currentPmSnapshotOutputPower']))
  {
    $descr = $ifDescr . " Output Power";
    $oid   = ".1.3.6.1.4.1.2544.1.11.2.6.2.156.1.1.".$index;
    $value = $entry['currentPmSnapshotOutputPower'];

    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'adva-output-power', $descr, $scale, $value, $options);
  }

  // Input Power
  //FspR7-MIB::currentPmSnapshotInputPower.269092419 = -990
  //FspR7-MIB::currentPmSnapshotInputPower.269092609 = -42
  //FspR7-MIB::currentPmSnapshotInputPower.269092673 = -120
  if (is_numeric($entry['currentPmSnapshotInputPower']))
  {
    $descr = $ifDescr . " Input Power";
    $oid   = ".1.3.6.1.4.1.2544.1.11.2.6.2.156.1.2.".$index;
    $value = $entry['currentPmSnapshotInputPower'];
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'adva-input-power', $descr, $scale, $value, $options);
  }

  // Rx Line Attenuation
  //FspR7-MIB::currentPmSnapshotRxLineAttenuation.252314434 = 67
  //FspR7-MIB::currentPmSnapshotRxLineAttenuation.252314435 = 75
  if (is_numeric($entry['currentPmSnapshotRxLineAttenuation']))
  {
    $descr = $ifDescr . " Rx Line Attenuation";
    $oid   = ".1.3.6.1.4.1.2544.1.11.2.6.2.156.1.11.".$index;
    $value = $entry['currentPmSnapshotRxLineAttenuation'];

    discover_sensor($valid['sensor'], 'snr', $device, $oid, $index, 'adva-rx-attenuation', $descr, $scale, $value, $options);
  }

  // Tx Line Attenuation
  //FspR7-MIB::currentPmSnapshotTxLineAttenuation.252314434 = 82
  //FspR7-MIB::currentPmSnapshotTxLineAttenuation.252314435 = 73
  if (is_numeric($entry['currentPmSnapshotTxLineAttenuation']))
  {
    $descr = $ifDescr . " Tx Line Attenuation";
    $oid   = ".1.3.6.1.4.1.2544.1.11.2.6.2.156.1.10.".$index;
    $value = $entry['currentPmSnapshotTxLineAttenuation'];

    discover_sensor($valid['sensor'], 'snr', $device, $oid, $index, 'adva-tx-attenuation', $descr, $scale, $value, $options);
  }
}

// EOF
