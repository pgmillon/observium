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

//PAN-COMMON-MIB::panSysSwVersion.0 = STRING: 3.1.10
//PAN-COMMON-MIB::panSysHwVersion.0 = STRING: 2.0
//PAN-COMMON-MIB::panSysSerialNumber.0 = STRING: 0004C10xxxx
//PAN-COMMON-MIB::panSysTimeZoneOffset.0 = INTEGER: 32400
//PAN-COMMON-MIB::panSysDaylightSaving.0 = INTEGER: 0
//PAN-COMMON-MIB::panSysVpnClientVersion.0 = STRING: 0.0.0
//PAN-COMMON-MIB::panSysAppVersion.0 = STRING: 430-2169
//PAN-COMMON-MIB::panSysAvVersion.0 = STRING: 1151-1607
//PAN-COMMON-MIB::panSysThreatVersion.0 = STRING: 405-2020

$hardware  = trim(snmp_get($device, 'panChassisType.0',     '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');
$version   = trim(snmp_get($device, 'panSysSwVersion.0',    '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');
$features  = trim(snmp_get($device, 'panSysHwVersion.0',    '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');
$serial    = trim(snmp_get($device, 'panSysSerialNumber.0', '-OQv', 'PAN-COMMON-MIB', mib_dirs('paloalto')),'" ');

// EOF
