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

// Sentry3-MIB::towerModelNumber        "CW-24V2-L30M"
// Sentry3-MIB::systemVersion        "Sentry Switched CDU Version 6.0g"
// Sentry3-MIB::towerProductSN        "ABEF0001561"

$hardware = snmp_get($device, "towerModelNumber.1", "-Ovq", "Sentry3-MIB");
$serial = snmp_get($device, "towerProductSN.1", "-Ovq", "Sentry3-MIB");
$version = snmp_get($device, "systemVersion.0", "-Ovq", "Sentry3-MIB");
list(, $version) = explode('Version ', $version);

// EOF
