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

// SNMPv2-SMI::enterprises.2620.1.6.4.1.0 = STRING: "R76"

$mib = 'CHECKPOINT-MIB';

$version  = snmp_get($device, 'svnVersion.0', '-OQv', $mib);
$hardware = snmp_get($device, 'svnApplianceProductName.0', '-OQv', $mib);
$serial   = snmp_get($device, 'svnApplianceSerialNumber.0', '-OQv', $mib);
$features = snmp_get($device, 'haState.0', '-OQv', $mib);

if (empty($hardware)) // Fallback since svnApplianceProductName is only supported since R77.10
{
  $hardware = rewrite_unix_hardware($poll_device['sysDescr']);
}

// EOF
