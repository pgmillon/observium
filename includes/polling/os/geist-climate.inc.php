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

// FIXME in case we discover web interfaces later on: GEIST-V4-MIB::productUrl.0 = STRING: http://1.2.3.4

$version  = snmp_get($device, "productVersion.0", "-Ovq", "GEIST-V4-MIB", mib_dirs('geist'));
$hardware = "Geist " . snmp_get($device, "productTitle.0", "-Ovq", "GEIST-V4-MIB", mib_dirs('geist'));

// EOF
