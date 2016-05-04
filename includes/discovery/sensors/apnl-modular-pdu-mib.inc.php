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

// APNL-MODULAR-PDU-MIB

echo(" APNL-MODULAR-PDU-MIB ");

// Power

$oids_power = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodePower", array(), "APNL-MODULAR-PDU-MIB");

foreach ($oids_power as $index => $entry)
{
  $descr = "Node $index";
  $oid   = ".1.3.6.1.4.1.29640.4.3.33.1.5.$index";
  $value = $entry['nodePower'];
  if (is_numeric($value) && $value > 0)
  {
// Disabled due to the fact thats it's Kwh instead of just Watt - not supported yet.
//      discover_sensor($valid['sensor'], 'power', $device, $oid, $index, 'apnl-modular-pdu-mib', $descr, 1, $value);
  }
}

// Frequency

$oids_freq = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodeFrequency", array(), "APNL-MODULAR-PDU-MIB");

foreach ($oids_freq as $index => $entry)
{
  $descr = "Node $index";
  $oid   = ".1.3.6.1.4.1.29640.4.3.33.1.11.$index";
  $value = $entry['nodeFrequency'];
  if (is_numeric($value) && $value > 0)
  {
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'apnl-modular-pdu-mib', $descr, 0.1, $value);
  }
}

// Voltage

$oids_volt = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodeVoltage", array(), "APNL-MODULAR-PDU-MIB");
$oids_volt = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodeMinVoltage", $oids_volt, "APNL-MODULAR-PDU-MIB");

foreach ($oids_volt as $index => $entry)
{
  $descr  = "Node $index";
  $oid    = ".1.3.6.1.4.1.29640.4.3.33.1.8.$index";
  $value  = $entry['nodeVoltage'];
  $limits = array('limit_low' => $entry['nodeMinVoltage']);

  if (is_numeric($value) && $value > 0)
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'apnl-modular-pdu-mib', $descr, 1, $value, $limits);
  }
}

// Amperes

$oids_curr = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodeAcurrent", array(), "APNL-MODULAR-PDU-MIB");
$oids_curr = snmpwalk_cache_multi_oid($device, "pdu.nodeTable.nodeEntry.nodePeakCurrent", $oids_curr, "APNL-MODULAR-PDU-MIB");

foreach ($oids_curr as $index => $entry)
{
  $descr  = "Node $index";
  $oid    = ".1.3.6.1.4.1.29640.4.3.33.1.6.$index";
  $value  = $entry['nodeAcurrent'];
  $limits = array('limit_high' => $entry['nodePeakCurrent']);

  if (is_numeric($value) && $value > 0)
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'apnl-modular-pdu-mib', $descr, 0.1, $value, $limits);
  }
}

// EOF
