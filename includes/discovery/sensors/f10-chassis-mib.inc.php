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

// Force10 E-Series

#F10-CHASSIS-MIB::chSysCardType.1 = INTEGER: rpmCardEF3(206)
#F10-CHASSIS-MIB::chSysCardType.3 = INTEGER: lc2401E24PG3(69)
#F10-CHASSIS-MIB::chSysCardType.4 = INTEGER: lc2401E24PG3(69)
#F10-CHASSIS-MIB::chSysCardUpperTemp.1 = Gauge32: 34
#F10-CHASSIS-MIB::chSysCardUpperTemp.3 = Gauge32: 34
#F10-CHASSIS-MIB::chSysCardUpperTemp.4 = Gauge32: 34

echo(" F10-CHASSIS-MIB ");

$sensor_state_type = 'f10-chassis-state';

// Temperatures
$oids = snmpwalk_cache_oid($device, "chSysCardUpperTemp", array(), "F10-CHASSIS-MIB", mib_dirs('force10'));

foreach ($oids as $index => $entry)
{
  $descr = "Slot ".$index;
  $oid   = ".1.3.6.1.4.1.6027.3.1.1.2.3.1.8.$index";
  $value = $entry['chSysCardUpperTemp'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-eseries', $descr, 1, $value);
}

// Supply
$oids = snmpwalk_cache_oid($device, "chSysPowerSupplyOperStatus", array(), "F10-CHASSIS-MIB", mib_dirs('force10'));

foreach ($oids as $index => $entry)
{
  $descr = "Power Supply ".$index;
  $oid   = ".1.3.6.1.4.1.6027.3.1.1.2.1.1.2.$index";
  $value = $entry['chSysPowerSupplyOperStatus'];
  discover_sensor($valid['sensor'], 'state', $device, $oid, 'supply-'.$index, $sensor_state_type, $descr, NULL, $value, array('entPhysicalClass' => 'power'));
}

// EOF
