<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// This could probably do with a rewrite, I suspect there's 1 table that can be walked for all the info below instead of 4.
// Also, all types should be equal, not brocade-dom, brocade-dom-tx and brocade-dom-rx (requires better indexes too)

echo(" FOUNDRY-SN-SWITCH-GROUP-MIB ");

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTxBiasCurrent", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

$scale = si_to_scale('milli');
foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.$index","-Oqv") . " DOM TX Bias Current";
  $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.4.$index";
  $value   = $entry['snIfOpticalMonitoringTxBiasCurrent'];
  $options = array('entPhysicalIndex' => $index);
  $port     = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (!preg_match("|N/A|", $value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'brocade-dom', $descr, $scale, $value * $scale, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTxPower", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.$index","-Oqv") . " DOM TX Power";
  $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.2.$index";
  $value   =  $entry['snIfOpticalMonitoringTxPower'];
  $options = array('entPhysicalIndex' => $index);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (!preg_match("|N/A|", $value))
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade-dom-tx', $descr, 1, $value, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringRxPower", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.$index","-Oqv") . " DOM RX Power";
  $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.3.$index";
  $value   = $entry['snIfOpticalMonitoringRxPower'];
  $options = array('entPhysicalIndex' => $index);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (!preg_match("|N/A|", $value))
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade-dom-rx', $descr, 1, $value, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTemperature", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.$index","-Oqv") . " DOM Temperature";
  $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.1.$index";
  $value   = $entry['snIfOpticalMonitoringTemperature'];
  $options = array('entPhysicalIndex' => $index);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (!preg_match("|N/A|",$value))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'brocade-dom', $descr, 1, $value, $options);
  }
}

// EOF
