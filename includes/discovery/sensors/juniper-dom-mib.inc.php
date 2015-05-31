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

echo(" JUNIPER-DOM-MIB ");

$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserBiasCurrent",                    array(), "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold",    $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold",     $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold",  $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold",   $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));

$scale = si_to_scale('micro');
foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.".$index,"-Oqv") . " tx bias current";
  $oid     = ".1.3.6.1.4.1.2636.3.60.1.1.1.1.6.".$index;
  $value   = $entry['jnxDomCurrentTxLaserBiasCurrent'];
  $options = array('entPhysicalIndex' => $index,
                   'limit_high'       => $entry['jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold']   * $scale,
                   'limit_low'        => $entry['jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold']    * $scale,
                   'limit_high_warn'  => $entry['jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold'] * $scale,
                   'limit_low_warn'   => $entry['jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold']  * $scale);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'juniper-dom', $descr, $scale, $value * $scale, $options);
  }
}

# jnxDomCurrentModuleTemperature[508] 35
# jnxDomCurrentModuleTemperatureHighAlarmThreshold[508] 100
# jnxDomCurrentModuleTemperatureLowAlarmThreshold[508] -25
# jnxDomCurrentModuleTemperatureHighWarningThreshold[508] 95
# jnxDomCurrentModuleTemperatureLowWarningThreshold[508] -20

$oids = snmpwalk_cache_oid($device, "jnxDomCurrentModuleTemperature", array(), "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentModuleTemperatureHighAlarmThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentModuleTemperatureLowAlarmThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentModuleTemperatureHighWarningThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentModuleTemperatureLowWarningThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));

foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.".$index,"-Oqv") . " DOM";
  $oid     = ".1.3.6.1.4.1.2636.3.60.1.1.1.1.8.".$index;
  $value   = $entry['jnxDomCurrentModuleTemperature'];
  $options = array('entPhysicalIndex' => $index,
                   'limit_high'       => $entry['jnxDomCurrentModuleTemperatureHighAlarmThreshold'],
                   'limit_low'        => $entry['jnxDomCurrentModuleTemperatureLowAlarmThreshold'],
                   'limit_high_warn'  => $entry['jnxDomCurrentModuleTemperatureHighWarningThreshold'],
                   'limit_low_warn'   => $entry['jnxDomCurrentModuleTemperatureLowWarningThreshold']);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'juniper-dom', $descr, 1, $value, $options);
  }
}

# jnxDomCurrentRxLaserPower[508] -507 0.01 dbm

$oids = snmpwalk_cache_oid($device, "jnxDomCurrentRxLaserPower",                  array(), "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentRxLaserPowerHighAlarmThreshold",  $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentRxLaserPowerLowAlarmThreshold",   $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentRxLaserPowerHighWarningThreshold",$oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentRxLaserPowerLowWarningThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));

$scale = 0.01;
foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.".$index,"-Oqv") . " rx power";
  $oid     = ".1.3.6.1.4.1.2636.3.60.1.1.1.1.5.".$index;
  $value   = $entry['jnxDomCurrentRxLaserPower'];
  $options = array('entPhysicalIndex' => $index,
                   'limit_high'       => $entry['jnxDomCurrentRxLaserPowerHighAlarmThreshold']   * $scale,
                   'limit_low'        => $entry['jnxDomCurrentRxLaserPowerLowAlarmThreshold']    * $scale,
                   'limit_high_warn'  => $entry['jnxDomCurrentRxLaserPowerHighWarningThreshold'] * $scale,
                   'limit_low_warn'   => $entry['jnxDomCurrentRxLaserPowerLowWarningThreshold']  * $scale);
  $port    = get_port_by_index_cache($device['device_id'], $index);
  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'juniper-dom-rx', $descr, $scale, $value * $scale, $options);
  }
}

# jnxDomCurrentTxLaserOutputPower[508] -507 0.01 dbm

$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserOutputPower",                  array(), "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold",  $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold",   $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserOutputPowerHighWarningThreshold",$oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));
$oids = snmpwalk_cache_oid($device, "jnxDomCurrentTxLaserOutputPowerLowWarningThreshold", $oids, "JUNIPER-DOM-MIB", mib_dirs('junos'));

$scale = 0.01;
foreach ($oids as $index => $entry)
{
  $descr   = snmp_get($device, "ifDescr.".$index,"-Oqv") . " tx output power";
  $oid     = ".1.3.6.1.4.1.2636.3.60.1.1.1.1.7.".$index;
  $value   = $entry['jnxDomCurrentTxLaserOutputPower'];
  $options = array('entPhysicalIndex' => $index,
                   'limit_high'       => $entry['jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold']   * $scale,
                   'limit_low'        => $entry['jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold']    * $scale,
                   'limit_high_warn'  => $entry['jnxDomCurrentTxLaserOutputPowerHighWarningThreshold'] * $scale,
                   'limit_low_warn'   => $entry['jnxDomCurrentTxLaserOutputPowerLowWarningThreshold']  * $scale);
  $port    = get_port_by_index_cache($device['device_id'], $index);

  if (is_array($port))
  {
    $options['measured_class']  = 'port';
    $options['measured_entity'] = $port['port_id'];
  }

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'juniper-dom-tx', $descr, $scale, $value * $scale, $options);
  }
}

// EOF
