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

$version  = trim(snmp_get($device, "swFirmwareVersion.0", "-Ovq", 'SW-MIB', mib_dirs('brocade')),'"');
$hardware = $entPhysical['entPhysicalDescr'];
$serial   = $entPhysical['entPhysicalSerialNum'];

// EOF
