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

echo(" EdgeSwitch-BOXSERVICES-PRIVATE-MIB ");

// Retrieve temperature limits
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMin.0 = INTEGER: -5
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesNormalTempRangeMax.0 = INTEGER: 85

$boxServicesNormalTempRangeMin = snmp_get($device, "boxServicesNormalTempRangeMin.0", "-Ovq", "EdgeSwitch-BOXSERVICES-PRIVATE-MIB", mib_dirs('ubiquiti'));
$boxServicesNormalTempRangeMax = snmp_get($device, "boxServicesNormalTempRangeMax.0", "-Ovq", "EdgeSwitch-BOXSERVICES-PRIVATE-MIB", mib_dirs('ubiquiti'));

/*
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.1.0 = Gauge32: 1
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesUnitIndex.1.1 = Gauge32: 1
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.1.0 = Gauge32: 0
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex.1.1 = Gauge32: 1
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.1.0 = INTEGER: fixed(1)
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorType.1.1 = INTEGER: fixed(1)
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.1.0 = INTEGER: 50
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature.1.1 = INTEGER: 33
EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempUnitState.1 = INTEGER: normal(1)
*/

$oids = snmpwalk_cache_multi_oid($device, "boxServicesTempSensorsTable", array(), "EdgeSwitch-BOXSERVICES-PRIVATE-MIB", mib_dirs('ubiquiti'));

foreach ($oids as $index => $entry)
{

    $boxServicesStackTempSensorsTable = TRUE;

    $descr = (count($oids) > 1 ? "Stack Unit " . $entry['boxServicesUnitIndex'] . " " : "") . "Internal Sensor " . $entry['boxServicesTempSensorIndex'];
    $oid = ".1.3.6.1.4.1.4413.1.1.43.1.8.1.5.". $index;
    $value = $entry['boxServicesTempSensorTemperature'];

    $options = array(
      'limit_low'        => $boxServicesNormalTempRangeMin,
      'limit_high'       => $boxServicesNormalTempRangeMax,
      'entPhysicalClass' => 'chassis',
    );

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "boxServicesTempSensorTemperature.$index", 'edgeswitch-boxservices-private-mib', $descr, 1, $value, $options);
}

// State indicator

$oids = snmpwalk_cache_oid($device, "boxServicesTempUnitState", array(), "EdgeSwitch-BOXSERVICES-PRIVATE-MIB", mib_dirs('ubiquiti'));

foreach ($oids as $index => $entry)
{
    $oid = ".1.3.6.1.4.1.4413.1.1.43.1.15.1.2." . $index;
    $value = $entry['boxServicesTempUnitState'];
    $descr = (count($oids) > 1 ? "Stack Unit " . $entry['index'] . " " : "") . "Temperature Status";

    discover_status($device, $oid, $index, 'fastpath-boxservices-private-temp-state', $descr, $value, array('entPhysicalClass' => 'chassis'));
}

//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyIndex.0 = INTEGER: 0
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemType.0 = INTEGER: fixed(1)
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesPowSupplyItemState.0 = INTEGER: operational(2)

$oids = snmpwalk_cache_multi_oid($device, "boxServicesPowSuppliesTable", array(), "EdgeSwitch-BOXSERVICES-PRIVATE-MIB", mib_dirs('ubiquiti'));

foreach ($oids as $index => $entry)
{
  $descr = ucfirst($entry['boxServicesPowSupplyItemType'] . ' Power Supply ' ) . $index;
  $oid   = ".1.3.6.1.4.1.4413.1.1.43.1.7.1.3.".$index;
  $value = snmp_get($device, $oid, "-Oqv");

  if ($entry['boxServicesPowSupplyItemState'] != 'notpresent')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "boxServicesPowSupplyItemState.$index", 'fastpath-boxservices-private-state', $descr, 1, $value, array('entPhysicalClass' => 'power'));
  }
}

// EOF
