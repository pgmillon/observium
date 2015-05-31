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

$version = trim(snmp_get($device, 'swFirmwareVersion.0', '-Ovq', 'SW-MIB', mib_dirs('brocade')),'"');
$hardware = trim(snmp_get($device, 'entPhysicalDescr.1', '-Ovq', 'ENTITY-MIB', mib_dirs()),'"');
$serial = trim(snmp_get($device, 'entPhysicalSerialNum.1', '-Ovq', 'ENTITY-MIB', mib_dirs()),'"');

// EOF
