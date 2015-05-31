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

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.2.0", "-OQv", "", ""),'" ');
$version = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.3.0", "-OQv", "", ""),'" ');
$serial = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.4.0", "-OQv", "", ""),'" ');

// EOF
