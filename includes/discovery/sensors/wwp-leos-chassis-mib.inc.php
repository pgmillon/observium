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

// WWP-LEOS-CHASSIS-MIB::wwpLeosChassisTempSensorTable

echo(" WWP-LEOS-CHASSIS-MIB ");

$value  = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.11.1.1.5.1.1.2.1", "-Oqv");
$limits = array('limit_high' => snmp_get($device, ".1.3.6.1.4.1.6141.2.60.11.1.1.5.1.1.3.1", "-Oqv"),
                 'limit_low'  => snmp_get($device, ".1.3.6.1.4.1.6141.2.60.11.1.1.5.1.1.4.1", "-Oqv"));
$descr  = "Chassis Temp";
$oid    = ".1.3.6.1.4.1.6141.2.60.11.1.1.5.1.1.2.1";

discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'Ciena', $descr, 1, $value, $limits);

// EOF
