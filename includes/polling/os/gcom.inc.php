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

$mib = "GBNPlatformOAM-MIB";

// GBNPlatformOAM-MIB::productName.0 = STRING: EL5600-04P EPON Product
$hardware = str_replace(" Product", "", snmp_get($device, 'productName.0', '-Osqv', $mib, mib_dirs('gcom')));

// GBNPlatformOAM-MIB::softwareVersion.0 = STRING: EL5600-04P V100R001B01D001P001SP13
$version  = snmp_get($device, 'softwareVersion.0', '-Osqv', $mib, mib_dirs('gcom'));
preg_match("/(V.*)/", $version, $matches);
if ($matches[1]) { $version = $matches[1]; }

// GBNPlatformOAM-MIB::prodSerialNo.0 = STRING: 012200040000xxxxxxxxxxxxxx
$serial   = snmp_get($device, 'prodSerialNo.0', '-Osqv', $mib, mib_dirs('gcom'));

// EOF
