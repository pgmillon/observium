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

// SNMPv2-SMI::enterprises.2620.1.6.4.1.0 = STRING: "R76"

$version = trim(snmp_get($device, "svnVersion.0", "-OQv", 'CHECKPOINT-MIB', mib_dirs('checkpoint')),'"');
$hardware = trim(snmp_get($device, "svnApplianceProductName.0", "-OQv", 'CHECKPOINT-MIB', mib_dirs('checkpoint')),'"');
$serial = trim(snmp_get($device, "svnApplianceSerialNumber.0", "-OQv", 'CHECKPOINT-MIB', mib_dirs('checkpoint')),'"');
$features = trim(snmp_get($device, "haState.0", "-OQv", 'CHECKPOINT-MIB', mib_dirs('checkpoint')),'"');

if (empty($hardware)) // Fallback since svnApplianceProductName is only supported since R77.10
{
  $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
}

// EOF
