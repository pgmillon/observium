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

echo(" Dell-Vendor-MIB ");

// Dell-Vendor-MIB::envMonFanStatusDescr.67109249 = STRING: fan1
// Dell-Vendor-MIB::envMonFanStatusDescr.67109250 = STRING: fan2
// Dell-Vendor-MIB::envMonFanState.67109249 = INTEGER: normal(1)
// Dell-Vendor-MIB::envMonFanState.67109250 = INTEGER: normal(1)

$oids = snmpwalk_cache_multi_oid($device, "envMonFanStatusTable", array(), "Dell-Vendor-MIB", mib_dirs('dell'));

foreach ($oids as $index => $entry)
{
  $descr = ucfirst($entry['envMonFanStatusDescr']);
  $oid   = ".1.3.6.1.4.1.674.10895.3000.1.2.110.7.1.1.3.".$index;
  $value = $entry['envMonFanState'];

  $query  = "SELECT sensor_id FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = 'fanspeed'";
  $query .= " AND `sensor_type` IN ('radlan-hwenvironment-state','fastpath-boxservices-private-state')";
  $query .= " AND (`sensor_index` IN (?) OR `sensor_descr` = ?)";

  if ($entry['envMonFanState'] != 'notPresent' && !count(dbFetchRows($query, array($device['device_id'], 'rlEnvMonFanState.'.$index, $descr))))
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "envMonFanState.$index", 'dell-vendor-state', $descr, NULL, $value, array('entPhysicalClass' => 'fan'));
  }
}

// Dell-Vendor-MIB::envMonSupplyStatusDescr.67109185 = STRING: ps1
// Dell-Vendor-MIB::envMonSupplyStatusDescr.67109186 = STRING: ps2
// Dell-Vendor-MIB::envMonSupplyState.67109185 = INTEGER: normal(1)
// Dell-Vendor-MIB::envMonSupplyState.67109186 = INTEGER: notPresent(5)
// Dell-Vendor-MIB::envMonSupplySource.67109185 = INTEGER: ac(2)
// Dell-Vendor-MIB::envMonSupplySource.67109186 = INTEGER: unknown(1)

$oids = snmpwalk_cache_multi_oid($device, "envMonSupplyStatusTable", array(), "Dell-Vendor-MIB", mib_dirs('dell'));

foreach ($oids as $index => $entry)
{
  $descr = ucfirst($entry['envMonSupplyStatusDescr']);
  $oid   = ".1.3.6.1.4.1.674.10895.3000.1.2.110.7.2.1.3.".$index;
  $value = $entry['envMonSupplyState'];

  // Ignore all PSU when we find another MIB has delivered. Descriptions and indexes are different so we cannot match them up.
  $query  = "SELECT sensor_id FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = 'power'";
  $query .= " AND `sensor_type` IN ('radlan-hwenvironment-state','fastpath-boxservices-private-state')";
  // FIXME This ignore doesn't seem to work, but dell vendor mib seems to have per-stackmember data as opposed to fastpath anyway.

  if ($entry['envMonSupplyState'] != 'notPresent' && !count(dbFetchRows($query, array($device['device_id']))))
  {
    // FIXME Is it possible to add stack member number to description?
    discover_sensor($valid['sensor'], 'state', $device, $oid, "envMonSupplyState.$index", 'dell-vendor-state', $descr, NULL, $value, array('entPhysicalClass' => 'power'));
  }
}

// EOF
