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

$hardware = trim(snmp_get($device, 'panChassisType.0', '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');
$version  = trim(snmp_get($device, 'panSysSwVersion.0', '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');
$serial   = trim(snmp_get($device, 'panSysSerialNumber.0', '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');

// EOF
