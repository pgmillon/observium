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

// SYNOLOGY-SYSTEM-MIB::version.0 = STRING: "DSM 4.2-3202"

// SYNOLOGY-SYSTEM-MIB::modelName.0 = STRING: "DS1513+"
// SYNOLOGY-SYSTEM-MIB::serialNumber.0 = STRING: "13A0LNN000123"
// SYNOLOGY-SYSTEM-MIB::version.0 = STRING: "DSM 5.0-4458"

$version = trim(snmp_get($device, 'version.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology')),'"');
$serial = trim(snmp_get($device, 'serialNumber.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology')),'"');
$hardware = trim(snmp_get($device, 'modelName.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology')),'"');

$version = str_replace('DSM','', $version);

// EOF
