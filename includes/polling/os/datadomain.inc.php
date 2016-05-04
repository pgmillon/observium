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

$mib = 'DATA-DOMAIN-MIB';

$version  = trim($poll_device['sysDescr'], 'Data Domain OS');
$serial   = snmp_get($device, 'systemSerialNumber.0', '-OQv', $mib);
$hardware = snmp_get($device, 'systemModelNumber.0', '-OQv', $mib);

// EOF
