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

// .1.3.6.1.4.1.32050.2.1.25.2.0 = STRING: "Base Unit II"
// .1.3.6.1.4.1.32050.2.1.25.3.0 = STRING: "Jul 29 2012"
// .1.3.6.1.4.1.32050.2.1.25.4.0 = INTEGER: 0

$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.32050.2.1.25.2.0", "-Oqvn", 'SNMPv2-SMI', mib_dirs()),'"');
$hardware = "SiteMonitor $hardware";
$version = trim(snmp_get($device, ".1.3.6.1.4.1.32050.2.1.25.3.0", "-Oqvn", 'SNMPv2-SMI', mib_dirs()),'"');
$serial = snmp_get($device, ".1.3.6.1.4.1.32050.2.1.25.4.0", "-Oqvn", 'SNMPv2-SMI', mib_dirs());
if (!$serial) { unset($serial); }

// EOF
