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

echo(" AC-SYSTEM-MIB ");

$sensor_type = 'ac-system';

//AC-SYSTEM-MIB::acSysFanTrayGeographicalPosition.1 = Gauge32: 1
//AC-SYSTEM-MIB::acSysFanTrayExistence.1 = INTEGER: present(1)
//AC-SYSTEM-MIB::acSysFanTrayType.1 = STRING: M1K's Fan-Tray ID.
//AC-SYSTEM-MIB::acSysFanTrayLEDs.1 = Hex-STRING: E2 EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE
//AC-SYSTEM-MIB::acSysFanTraySeverity.1 = INTEGER: cleared(0)
//AC-SYSTEM-MIB::acSysFanTrayFansConfiguredSpeed.1 = Hex-STRING: 00 00 00 00 00 00 00 00
//AC-SYSTEM-MIB::acSysFanTrayFansCurrentSpeed.1 = Hex-STRING: 00 00 00 00 00 00 00 00
//AC-SYSTEM-MIB::acSysFanTrayFansStatus.1 = Hex-STRING: 00

$oids = snmpwalk_cache_multi_oid($device, "acSysFanTrayTable", array(), "AC-SYSTEM-MIB", mib_dirs('audiocodes'));

$sensor_state_type = $sensor_type.'-fan-state';
foreach ($oids as $index => $entry)
{
  $descr = "Fan Tray $index";
  $oid   = ".1.3.6.1.4.1.5003.9.10.10.4.22.1.6.$index";

  if ($entry['acSysFanTrayExistence'] != 'missing')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "acSysFanTray.$index", $sensor_state_type, $descr, NULL, $entry['acSysFanTraySeverity'], array('entPhysicalClass' => 'fan'));
  }
}

// AC-SYSTEM-MIB::acSysPowerSupplyGeographicalPosition.1 = Gauge32: 1
// AC-SYSTEM-MIB::acSysPowerSupplyGeographicalPosition.2 = Gauge32: 2
// AC-SYSTEM-MIB::acSysPowerSupplyExistence.1 = INTEGER: present(1)
// AC-SYSTEM-MIB::acSysPowerSupplyExistence.2 = INTEGER: present(1)
// AC-SYSTEM-MIB::acSysPowerSupplyHwversion.1 = STRING:
// AC-SYSTEM-MIB::acSysPowerSupplyHwversion.2 = STRING:
// AC-SYSTEM-MIB::acSysPowerSupplyLEDs.1 = Hex-STRING: E2 EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE
// AC-SYSTEM-MIB::acSysPowerSupplyLEDs.2 = Hex-STRING: E2 EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE EE
// AC-SYSTEM-MIB::acSysPowerSupplySeverity.1 = INTEGER: cleared(1)
// AC-SYSTEM-MIB::acSysPowerSupplySeverity.2 = INTEGER: cleared(1)

$oids = snmpwalk_cache_multi_oid($device, "acSysPowerSupplyTable", array(), "AC-SYSTEM-MIB", mib_dirs('audiocodes'));

$sensor_state_type = $sensor_type.'-power-state';
foreach ($oids as $index => $entry)
{
  $descr = "Power Supply $index";
  $oid   = ".1.3.6.1.4.1.5003.9.10.10.4.23.1.6.$index";

  if ($entry['acSysPowerSupplyExistence'] != 'missing')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "acSysPowerSupply.$index", $sensor_state_type, $descr, NULL, $entry['acSysPowerSupplySeverity'], array('entPhysicalClass' => 'power'));
  }
}

// AC-SYSTEM-MIB::acSysModuleTemperature.67911681 = INTEGER: -1
// AC-SYSTEM-MIB::acSysModuleTemperature.68173825 = INTEGER: -1
// AC-SYSTEM-MIB::acSysModuleTemperature.68435969 = INTEGER: -1
// AC-SYSTEM-MIB::acSysModuleTemperature.68956161 = INTEGER: -1
// AC-SYSTEM-MIB::acSysModuleType.67911681 = INTEGER: acMediant1000IPMediaModule(257)
// AC-SYSTEM-MIB::acSysModuleType.68173825 = INTEGER: acMediant1000IPMediaModule(257)
// AC-SYSTEM-MIB::acSysModuleType.68435969 = INTEGER: acMediant1000IPMediaModule(257)
// AC-SYSTEM-MIB::acSysModuleType.68956161 = INTEGER: acMediant1000CPUmodule(253)
// AC-SYSTEM-MIB::acSysModulePresence.67911681 = INTEGER: present(1)
// AC-SYSTEM-MIB::acSysModulePresence.68173825 = INTEGER: present(1)
// AC-SYSTEM-MIB::acSysModulePresence.68435969 = INTEGER: present(1)
// AC-SYSTEM-MIB::acSysModulePresence.68956161 = INTEGER: present(1)

$oids = snmpwalk_cache_multi_oid($device, "acSysModuleTemperature", array(), "AC-SYSTEM-MIB", mib_dirs('audiocodes'));
$oids = snmpwalk_cache_multi_oid($device, "acSysModuleType",          $oids, "AC-SYSTEM-MIB", mib_dirs('audiocodes'));
$oids = snmpwalk_cache_multi_oid($device, "acSysModulePresence",      $oids, "AC-SYSTEM-MIB", mib_dirs('audiocodes'));

foreach ($oids as $index => $entry)
{
  $descr = substr($entry['acSysModuleType'], 2);
  $oid   = ".1.3.6.1.4.1.5003.9.10.10.4.21.1.11.$index";
  $value = $entry['acSysModuleTemperature'];

  if ($entry['acSysModulePresence'] != 'missing' && stripos($entry['acSysModuleType'], 'sA') !== 0 && $value > 0)
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "acSysModuleTemperature.$index", $sensor_type, $descr, 1, $value, array('limit_high' => 60));
  }
}

// EOF
