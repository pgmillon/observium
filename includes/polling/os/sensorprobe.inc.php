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

$hardware = trim(snmp_get($device, "spManufName.0", "-OQv", "SPAGENT-MIB", mib_dirs('akcp')),'"');
$hardware .= ' ' . trim(snmp_get($device, "spProductName.0", "-OQv", "SPAGENT-MIB", mib_dirs('akcp')),'" ');

// EOF
