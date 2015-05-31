<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2014 Adam Armstrong
 *
 */

$version  = trim(snmp_get($device, 'softwareVersion.0', '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium')),'"');
$hardware = trim(snmp_get($device, 'productName.0', '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium')),'"');
$serial   = trim(snmp_get($device, 'rFUSerial.0', '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium')),'"');

// EOF
