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

// FIXME in case we discover web interfaces later on: GEIST-MIB-V3::productUrl.0 = STRING: http://1.2.3.4

$version  = snmp_get($device, "productVersion.0", "-Ovq", "GEIST-MIB-V3", mib_dirs('geist'));
$hardware = snmp_get($device, "productHardware.0", "-Ovq", "GEIST-MIB-V3", mib_dirs('geist')) . " " . snmp_get($device, "productTitle.0", "-Ovq", "GEIST-MIB-V3", mib_dirs('geist'));

$hardware = str_replace("GEIST","Geist",$hardware);

// EOF
