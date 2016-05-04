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

echo(" CPQHLTH-MIB ");

// Power Supplies

$oids = snmpwalk_cache_oid($device, "cpqHeFltTolPwrSupply", array(), "CPQHLTH-MIB", mib_dirs('hp'));

foreach ($oids as $index => $entry)
{
  if (isset($entry['cpqHeFltTolPowerSupplyBay']))
  {
    $descr      = "PSU ".$entry['cpqHeFltTolPowerSupplyBay'];
    $oid        = ".1.3.6.1.4.1.232.6.2.9.3.1.7.".$index;
    $value      = $entry['cpqHeFltTolPowerSupplyCapacityUsed'];
    $limits     = array('limit_high' => $entry['cpqHeFltTolPowerSupplyCapacityMaximum']);

    discover_sensor($valid['sensor'], 'power', $device, $oid, 'cpqHeFltTolPwrSupply.'.$index, 'cpqhlth', $descr, 1, $value, $limits);
  }

  if (isset($entry['cpqHeFltTolPowerSupplyCondition']))
  {
    $descr      = $descr." Status";
    $oid        = ".1.3.6.1.4.1.232.6.2.9.3.1.4.".$index;
    $value      = $entry['cpqHeFltTolPowerSupplyCondition'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqHeFltTolPwrSupply.'.$index, 'cpqhlth-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
  }
}

// Overal System Thermal Status

$thermal_status    = snmp_get($device, "cpqHeThermalCondition.0",       "-Ovq", "CPQHLTH-MIB", mib_dirs('hp'));
$system_fan_status = snmp_get($device, "cpqHeThermalSystemFanStatus.0", "-Ovq", "CPQHLTH-MIB", mib_dirs('hp'));
$cpu_fan_status    = snmp_get($device, "cpqHeThermalCpuFanStatus.0",    "-Ovq", "CPQHLTH-MIB", mib_dirs('hp'));

if ($thermal_status)
{
  $descr = "Thermal Status";
  $oid   = ".1.3.6.1.4.1.232.6.2.6.1.0";
  $value = $thermal_status;
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqHeThermalCondition.0', 'cpqhlth-state', $descr, NULL, $value, array('entPhysicalClass' => 'temperature'));
}

if ($system_fan_status)
{
  $descr = "System Fan Status";
  $oid   = ".1.3.6.1.4.1.232.6.2.6.4.0";
  $value = $system_fan_status;
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqHeThermalSystemFanStatus.0', 'cpqhlth-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

if ($cpu_fan_status)
{
  $descr = "CPU Fan Status";
  $oid   = ".1.3.6.1.4.1.232.6.2.6.5.0";
  $value = $cpu_fan_status;
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqHeThermalCpuFanStatus.0', 'cpqhlth-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
}

// Temperatures

$oids = snmpwalk_cache_oid($device, "CpqHeTemperatureEntry", array(), "CPQHLTH-MIB", mib_dirs('hp'));

$descPatterns = array('/Cpu/', '/PowerSupply/');
$descReplace = array('CPU', 'PSU');
$descCount = array('CPU' => 1, 'PSU' => 1);

foreach ($oids as $index => $entry)
{
  if ($entry['cpqHeTemperatureThreshold'] > 0)
  {
    $descr   = ucfirst($entry['cpqHeTemperatureLocale']);

    if ($descr === 'System' || $descr === 'Memory') { continue; }
    if ($descr === 'Cpu' || $descr === 'PowerSupply')
    {
      $descr = preg_replace($descPatterns, $descReplace, $descr);
      $descr = $descr.' '.$descCount[$descr]++;
    }

    $oid        = ".1.3.6.1.4.1.232.6.2.6.8.1.4.".$index;
    $value      = $entry['cpqHeTemperatureCelsius'];
    $limits     = array('limit_high' =>$entry['cpqHeTemperatureThreshold']);

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'CpqHeTemperatureEntry.'.$index, 'cpqhlth', $descr, 1, $value, $limits);
  }
}

// EOF
