<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (is_device_mib($device, 'CAMBIUM-PTP800-MIB'))
{
  $version  = snmp_get($device, 'softwareVersion.0', '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium'));
  $hardware = snmp_get($device, 'productName.0',     '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium'));
  $serial   = snmp_get($device, 'rFUSerial.0',       '-OQv', 'CAMBIUM-PTP800-MIB', mib_dirs('cambium'));
}
else if (is_device_mib($device, 'MOTOROLA-PTP-MIB'))
{
  $version  = snmp_get($device, 'softwareVersion.0', '-OQv', 'MOTOROLA-PTP-MIB', mib_dirs('cambium'));
  $hardware = snmp_get($device, 'productName.0',     '-OQv', 'MOTOROLA-PTP-MIB', mib_dirs('cambium'));
} else {
  $hardware = rewrite_definition_hardware($device, $poll_device['sysObjectID']);
}

// EOF
