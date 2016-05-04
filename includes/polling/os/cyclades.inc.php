<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$hardware = snmp_get($device, "acsProductModel.0",    "-Osqv", "ACS-MIB");
$serial   = snmp_get($device, "acsSerialNumber.0",    "-Osqv", "ACS-MIB");
$version  = snmp_get($device, "acsFirmwareVersion.0", "-Osqv", "ACS-MIB");

// EOF
