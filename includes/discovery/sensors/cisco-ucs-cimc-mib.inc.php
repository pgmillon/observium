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

echo(" CISCO-UCS-CIMC-MIB ");

$sensors = array();

$sensors['mbinputpower']['desc'] = "System Board Input Power";
$sensors['mbinputpower']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.14.1.4.1";
$sensors['mbinputpower']['measurement'] = "power";
$sensors['mbinputpower']['type'] = "cimc";

$sensors['mbinputcurrent']['desc'] = "System Board Input Current";
$sensors['mbinputcurrent']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.14.1.8.1";
$sensors['mbinputcurrent']['measurement'] = "current";
$sensors['mbinputcurrent']['type'] = "cimc";

$sensors['mbinputvoltage']['desc'] = "System Board Input Voltage";
$sensors['mbinputvoltage']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.14.1.12.1";
$sensors['mbinputvoltage']['measurement'] = "voltage";
$sensors['mbinputvoltage']['type'] = "cimc";

$sensors['ambienttemp']['desc'] = "Ambient Temperature";
$sensors['ambienttemp']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.44.1.4.1";
$sensors['ambienttemp']['measurement'] = "temperature";
$sensors['ambienttemp']['type'] = "cimc";

$sensors['fronttemp']['desc'] = "Front Temperature";
$sensors['fronttemp']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.44.1.8.1";
$sensors['fronttemp']['measurement'] = "temperature";
$sensors['fronttemp']['type'] = "cimc";

$sensors['iohubtemp']['desc'] = "IO Hub Temperature";
$sensors['iohubtemp']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.44.1.13.1";
$sensors['iohubtemp']['measurement'] = "temperature";
$sensors['iohubtemp']['type'] = "cimc";

$sensors['reartemp']['desc'] = "Rear Temperature";
$sensors['reartemp']['oid'] = ".1.3.6.1.4.1.9.9.719.1.9.44.1.21.1";
$sensors['reartemp']['measurement'] = "temperature";
$sensors['reartemp']['type'] = "cimc";

foreach ($sensors as $index => $sensor)
{
  $value = snmp_get($device, $sensor['oid'], "-Oqv");

  // Only add sensors which are present
  if ($sensor['desc'] != "" && is_numeric($value))
  {
    discover_sensor($valid['sensor'], $sensor['measurement'], $device, $sensor['oid'], $index, $sensor['type'], $sensor['desc'], 1, $value);
  }
}

// table: CPU Temperature info, walk through all installed CPUs
$oids = snmpwalk_cache_oid($device, "cucsProcessorEnvStatsTemperature", array(), "CISCO-UNIFIED-COMPUTING-PROCESSOR-MIB", mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = "CPU ".$index." Temperature";
  $oid = ".1.3.6.1.4.1.9.9.719.1.41.2.1.10.".$index;

  // Only add sensors which are present
  if (is_numeric($entry['cucsProcessorEnvStatsTemperature']))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "cpu".$index, 'cimc', $descr, 1, $entry['cucsProcessorEnvStatsTemperature']);
  }
}

// EOF
