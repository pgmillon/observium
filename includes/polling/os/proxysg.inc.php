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

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.2.0", "-OQv", "", ""),'" ');
$version_string = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.3.0", "-OQv", "", ""),'" ');

list(,$version) = explode(": ", $version_string);
list($version) = explode(",", $version);
$version = str_replace("SGOS ", "", $version);

$serial = trim(snmp_get($device, "1.3.6.1.4.1.3417.2.11.1.4.0", "-OQv", "", ""),'" ');

// EOF
