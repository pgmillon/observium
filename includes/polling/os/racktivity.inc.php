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

// mFirmwareVersion.1.0 = STRING: 3.1.1.0
// mHardwareVersion.1.0 = STRING: 1.0.0.0
// mDeviceVersion.1.0 = STRING: 1.0.0.0

$version = snmp_get($device, "mFirmwareVersion.1", "-OQv", 'ES-RACKTIVITY-MIB', mib_dirs('racktivity'));

// sysDescr.0 = STRING: Racktivity AC2Meter.
$hardware = str_replace("Racktivity ","",trim($poll_device['sysDescr'],'.'));

// EOF
