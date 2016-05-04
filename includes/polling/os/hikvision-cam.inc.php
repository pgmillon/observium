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

// HIK-DEVICE-MIB::deviceType.0 = STRING: "DS-2CD2332-I"
// HIK-DEVICE-MIB::softwVersion.0 = STRING: "V5.2.0 build 140721"
$hardware = snmp_get($device, "deviceType.0",   "-Osqv", "HIK-DEVICE-MIB");

list($version) = explode(' ', snmp_get($device, "softwVersion.0", "-Osqv", "HIK-DEVICE-MIB"));
$version = str_replace('V', '', $version);

// EOF
