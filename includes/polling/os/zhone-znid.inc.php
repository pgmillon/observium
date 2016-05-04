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

$mib = "ZHNSYSTEM";

$hardware = snmp_get($device, 'modelNumber.0', '-Osqv', $mib, mib_dirs('zhone'));
// $serial   = snmp_get($device, '', '-Osqv', $mib, mib_dirs('zhone'));
$version = snmp_get($device, 'sysFirmwareVersion.0', '-Osqv', $mib, mib_dirs('zhone'));
// $features = snmp_get($device, '', '-Osqv', $mib, mib_dirs('zhone'));

// EOF
