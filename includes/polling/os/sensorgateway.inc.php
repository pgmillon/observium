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

$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.17095.1.4.0", "-OQv", "", mib_dirs('akcp')),'"');
$hardware .= ' ' . trim(snmp_get($device, ".1.3.6.1.4.1.17095.1.1.0", "-OQv", "", mib_dirs('akcp')),'" ');

// EOF
