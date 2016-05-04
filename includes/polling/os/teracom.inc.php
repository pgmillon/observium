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

/*
iso.3.6.1.4.1.38783.1.1.1.0 = STRING: "TCW240B SNMP Agent"
iso.3.6.1.4.1.38783.1.1.2.0 = STRING: "v1.14"
iso.3.6.1.4.1.38783.1.1.3.0 = Hex-STRING: 07 DF 07 16 10 37 2E 00
iso.3.6.1.4.1.38783.1.2.1.1.0 = Hex-STRING: D8 80 39 28 BE 87
iso.3.6.1.4.1.38783.1.2.1.2.0 = STRING: "TCW240B        "
*/

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.38783.1.1.1.0", "-OQv", "TERACOM-MIB", mib_dirs('teracom')),'"');
$hardware = str_replace("SNMP Agent", "I/O Controller", $hardware);
$version = trim(snmp_get($device, "1.3.6.1.4.1.38783.1.1.2.0", "-OQv", "TERACOM-MIB", mib_dirs('teracom')),'"');
$serial = trim(snmp_get($device, "1.3.6.1.4.1.38783.1.2.1.1.0", "-OQv", "TERACOM-MIB", mib_dirs('teracom')),'"');

// EOF
