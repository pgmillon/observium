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

// FIXME Rename code can go in r6500.

echo(" FASTPATH-BOXSERVICES-PRIVATE-MIB ");

// Retrieve temperature limits
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMin.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMax.0 = INTEGER: 57
$boxServicesNormalTempRangeMin = snmp_get($device, "boxServicesNormalTempRangeMin.0", "-Ovq", "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));
$boxServicesNormalTempRangeMax = snmp_get($device, "boxServicesNormalTempRangeMax.0", "-Ovq", "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));

// Initialize check variable to false
$boxServicesStackTempSensorsTable = FALSE;

// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.1.0 = INTEGER: 1
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.2.0 = INTEGER: 2
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorIndex.1.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorIndex.2.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorType.1.0 = INTEGER: fixed(1)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorType.2.0 = INTEGER: fixed(1)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorState.1.0 = INTEGER: normal(1)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorState.2.0 = INTEGER: normal(1)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorTemperature.1.0 = INTEGER: 28
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesStackTempSensorTemperature.2.0 = INTEGER: 27

$oids = snmpwalk_cache_multi_oid($device, "boxServicesStackTempSensorsTable", array(), "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));

foreach ($oids as $index => $entry)
{
    $boxServicesStackTempSensorsTable = TRUE;

    $descr = (count($oids) > 1 ? "Stack Unit " . $entry['boxServicesUnitIndex'] . " " : "") . "Internal Temperature";
    $oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.9.1.5.$index";
    $value = $entry['boxServicesStackTempSensorTemperature'];

    $options = array(
      'limit_low'        => $boxServicesNormalTempRangeMin,
      'limit_high'       => $boxServicesNormalTempRangeMax,
      'entPhysicalClass' => 'temperature',
    );

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "boxServicesStackTempSensorTemperature.$index", 'fastpath-boxservices-private-mib', $descr, 1, $value, $options);

    ## Rename code for older revisions
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-fastpath-boxservices-private-mib-boxServicesStackTempSensorTemperature.$index.rrd");

    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-powerconnect-$index.rrd");
    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); print_warning("Moved RRD"); }

    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-fastpath-boxservices-private-mib-boxServicesTempSensorTemperature." . ($index-1) . ".rrd");
    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); print_warning("Moved RRD"); }

    $oid   = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.9.1.4.$index";
    $value = $entry['boxServicesStackTempSensorState'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, "boxServicesStackTempSensorState.$index", 'fastpath-boxservices-private-temp-state', $descr, 1, $value, array('entPhysicalClass' => 'temperature'));
}

if (!$boxServicesStackTempSensorsTable)
{
  // This table has been obsoleted by boxServicesStackTempSensorsTable - run it only if we didn't find that table.
  $oids = snmpwalk_cache_multi_oid($device, "boxServicesTempSensorsTable", array(), "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));

  // FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.0 = INTEGER: 0
  // FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.0 = INTEGER: fixed(1)
  // FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorState.0 = INTEGER: normal(1)
  // FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.0 = INTEGER: 26

  foreach ($oids as $index => $entry)
  {
    $descr = "Internal Temperature";
    $oid = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.4.$index";
    $value = $entry['boxServicesTempSensorTemperature'];

    $options = array(
      'limit_low'        => $boxServicesNormalTempRangeMin,
      'limit_high'       => $boxServicesNormalTempRangeMax,
      'entPhysicalClass' => 'temperature',
    );

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "boxServicesTempSensorTemperature.$index", 'fastpath-boxservices-private-mib', $descr, 1, $value, $options);

    ## Rename code for older revisions
    $old_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-powerconnect-" . ($index+1) . ".rrd");
    $new_rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("sensor-temperature-fastpath-boxservices-private-mib-boxServicesTempSensorTemperature.$index.rrd");
    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); print_warning("Moved RRD"); }

    $oid   = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.3.$index";
    $value = $entry['boxServicesTempSensorState'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, "boxServicesTempSensorState.$index", 'fastpath-boxservices-private-temp-state', $descr, 1, $value, array('entPhysicalClass' => 'temperature'));
  }
}

// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.1 = INTEGER: 1
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.2 = INTEGER: 2
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.3 = INTEGER: 3
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex.4 = INTEGER: 4
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.0 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.1 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.2 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.3 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanItemState.4 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.1 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.2 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.3 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed.4 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.1 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.2 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.3 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesFanDutyLevel.4 = INTEGER: 0

$oids = snmpwalk_cache_multi_oid($device, "boxServicesFansTable", array(), "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));

foreach ($oids as $index => $entry)
{
  $descr = "Fan"; if (count($oids) > 1) { $descr .= " " . ($index+1); }
  $oid   = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.6.1.3.".$index;
  $value = $entry['boxServicesFanItemState'];

  if ($entry['boxServicesFanItemState'] != 'notpresent')
  {
    // FIXME should be a state sensor. subtype fanspeed. (or fanspeed sensor, subtype state)
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, "boxServicesFanItemState.$index", 'fastpath-boxservices-private-state', $descr, 1, $value, array('entPhysicalClass' => 'fan'));

    if ($entry['boxServicesFanSpeed'] != 0)
    {
      // FIXME - could add a fan speed sensor here, but none of my devices have non-zero values.
      // duty level is most likely a percentage?
    }
  }
}

// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyIndex.0 = INTEGER: 0
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyIndex.1 = INTEGER: 1
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemType.0 = INTEGER: fixed(1)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemType.1 = INTEGER: removable(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemState.0 = INTEGER: operational(2)
// FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemState.1 = INTEGER: operational(2)

$oids = snmpwalk_cache_multi_oid($device, "boxServicesPowSuppliesTable", array(), "FASTPATH-BOXSERVICES-PRIVATE-MIB", mib_dirs('broadcom','dell'));

foreach ($oids as $index => $entry)
{
  $descr = ucfirst($entry['boxServicesPowSupplyItemType'] . ' Power Supply');
  $oid   = ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.7.1.3.".$index;
  $value = $entry['boxServicesPowSupplyItemState'];

  if ($entry['boxServicesPowSupplyItemState'] != 'notpresent')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "boxServicesPowSupplyItemState.$index", 'fastpath-boxservices-private-state', $descr, 1, $value, array('entPhysicalClass' => 'power'));
  }
}

// EOF
