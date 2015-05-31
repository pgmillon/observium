<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// DELL-RAC-MIB::drsFirmwareVersion.0 = STRING: "1.23.23"
// DELL-RAC-MIB::drsProductShortName.0 = STRING: "iDRAC7"
// DELL-RAC-MIB::drsSystemServiceTag.0 = STRING: "CGJ2H5J"

$version  = trim(snmp_get($device, "drsFirmwareVersion.0",  "-OQv", "DELL-RAC-MIB"),'"');
$hardware = trim(snmp_get($device, "drsProductShortName.0", "-OQv", "DELL-RAC-MIB"),'"');
$serial   = trim(snmp_get($device, "drsSystemServiceTag.0", "-OQv", "DELL-RAC-MIB"),'"');

// EOF
