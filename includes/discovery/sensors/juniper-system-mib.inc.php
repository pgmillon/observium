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

// JunOSe Temperatures

echo(" Juniper-System-MIB ");

$oids = snmpwalk_cache_multi_oid($device, "juniSystemTempValue", array(), "Juniper-System-MIB", mib_dirs('junose'));

foreach ($oids as $index => $entry)
{
  if (is_numeric($entry['juniSystemTempValue']) && is_numeric($index) && $entry['juniSystemTempValue'] > "0")
  {
    $entPhysicalIndex = snmp_get($device, "juniSystemTempPhysicalIndex.".$index, "-Oqv", "Juniper-System-MIB", mib_dirs('junose'));
    $descr = snmp_get($device, "entPhysicalDescr.".$entPhysicalIndex, "-Oqv", "ENTITY-MIB");
    $descr = preg_replace("/^Juniper\ [0-9a-zA-Z\-]+/", "", $descr); // Wipe out ugly Juniper crap. Why put vendor and model in here? Idiots!
    $descr = str_replace("temperature sensor on", "", trim($descr));
    $oid   = ".1.3.6.1.4.1.4874.2.2.2.1.9.4.1.3.".$index;
    $value = $entry['juniSystemTempValue'];

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'junose', $descr, 1, $value);
  }
}

// EOF
