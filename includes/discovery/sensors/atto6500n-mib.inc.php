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


echo " ATTO6500N-MIB ";

$value = snmp_get($device, "chassisTemperature.0", "-Ovq", "ATTO6500N-MIB");

$limit_low = snmp_get($device,"minimumOperatingTemp.0", "-Ovq", "ATTO6500N-MIB");
$limit_high = snmp_get($device,"minimumOperatingTemp.0", "-Ovq", "ATTO6500N-MIB");

$limits = array('limit_low'  => $limit_low, 
		'limit_high' => $limit_high);

$oid = ".1.3.6.1.4.1.4547.2.3.2.8.0";

// FIXME. Move to definitions, when limits will added
discover_sensor($valid['sensor'], "temperature", $device, $oid, "chassisTemperature.0", "ATTO6500N-MIB", "Chassis Temperature", 1, $value, $limits);
