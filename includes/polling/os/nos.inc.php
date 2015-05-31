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

$version  = trim(snmp_get($device, "swFirmwareVersion.0", "-Ovq", 'SW-MIB', mib_dirs('brocade')),'"');
$hardware = $entPhysical['entPhysicalDescr'];
$serial   = $entPhysical['entPhysicalSerialNum'];

// EOF
