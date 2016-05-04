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

// This could probably do with a rewrite, I suspect there's 1 table that can be walked for all the info below instead of 4.
// Also, all types should be equal, not brocade-dom, brocade-dom-tx and brocade-dom-rx (requires better indexes too)

echo(" FOUNDRY-SN-SWITCH-GROUP-MIB ");

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTxBiasCurrent", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

$scale = si_to_scale('milli');
foreach ($oids as $index => $entry)
{
  $value   = $entry['snIfOpticalMonitoringTxBiasCurrent'];
  if (!preg_match("|N/A|", $value))
  {
    //$descr   = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB") . " DOM TX Bias Current";
    $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.4.$index";
    $options = array('entPhysicalIndex' => $index);
    $port    = get_port_by_index_cache($device['device_id'], $index);

    if (is_array($port))
    {
      $descr = ($port["ifDescr"] ? $port["ifDescr"] : $port["ifName"]);
      $options['measured_class']  = 'port';
      $options['measured_entity'] = $port['port_id'];
    } else {
      $descr = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB");
    }
    $descr  .= " DOM TX Bias Current";

    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'brocade-dom', $descr, $scale, $value, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTxPower", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $value   =  $entry['snIfOpticalMonitoringTxPower'];
  if (!preg_match("|N/A|", $value))
  {
    //$descr   = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB") . " DOM TX Power";
    $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.2.$index";
    $options = array('entPhysicalIndex' => $index);
    $port    = get_port_by_index_cache($device['device_id'], $index);

    if (is_array($port))
    {
      $descr = ($port["ifDescr"] ? $port["ifDescr"] : $port["ifName"]);
      $options['measured_class']  = 'port';
      $options['measured_entity'] = $port['port_id'];
    } else {
      $descr = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB");
    }
    $descr  .= " DOM TX Power";

    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade-dom-tx', $descr, 1, $value, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringRxPower", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $value   = $entry['snIfOpticalMonitoringRxPower'];
  if (!preg_match("|N/A|", $value))
  {
    //$descr   = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB") . " DOM RX Power";
    $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.3.$index";
    $options = array('entPhysicalIndex' => $index);
    $port    = get_port_by_index_cache($device['device_id'], $index);

    if (is_array($port))
    {
      $descr = ($port["ifDescr"] ? $port["ifDescr"] : $port["ifName"]);
      $options['measured_class']  = 'port';
      $options['measured_entity'] = $port['port_id'];
    } else {
      $descr = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB");
    }
    $descr  .= " DOM RX Power";

    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade-dom-rx', $descr, 1, $value, $options);
  }
}

$oids = snmpwalk_cache_oid($device, "snIfOpticalMonitoringTemperature", array(), "FOUNDRY-SN-SWITCH-GROUP-MIB", mib_dirs('foundry'));

foreach ($oids as $index => $entry)
{
  $value   = $entry['snIfOpticalMonitoringTemperature'];
  if (!preg_match("|N/A|", $value))
  {
    //$descr   = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB") . " DOM Temperature";
    $oid     = ".1.3.6.1.4.1.1991.1.1.3.3.6.1.1.$index";
    $options = array('entPhysicalIndex' => $index);
    $port    = get_port_by_index_cache($device['device_id'], $index);

    if (is_array($port))
    {
      $descr = ($port["ifDescr"] ? $port["ifDescr"] : $port["ifName"]);
      $options['measured_class']  = 'port';
      $options['measured_entity'] = $port['port_id'];
    } else {
      $descr = snmp_get($device, "ifDescr.$index", "-Oqv", "IF-MIB");
    }
    $descr  .= " DOM Temperature";

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'brocade-dom', $descr, 1, $value, $options);
  }
}

// EOF
