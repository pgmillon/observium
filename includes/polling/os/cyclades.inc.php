<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$hardware = snmp_get($device, "acsProductModel.0",    "-Osqv", "ACS-MIB");
$serial   = snmp_get($device, "acsSerialNumber.0",    "-Osqv", "ACS-MIB");
$version  = snmp_get($device, "acsFirmwareVersion.0", "-Osqv", "ACS-MIB");

// EOF
