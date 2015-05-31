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

$hardware = $entPhysical['entPhysicalModelName'];
$version  = $entPhysical['entPhysicalSoftwareRev'];

if (empty($hardware)) { $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB"); }

// EOF
