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

// Parse CIMC version number from sysDescr
if (preg_match('/Firmware Version ([^,]+) Copyright/', $poll_device['sysDescr'], $regexp_result))
{
  $version = $regexp_result[1];
}

$serial = trim(snmp_get($device, ".1.3.6.1.4.1.9.9.719.1.9.6.1.14.1", "-Oqv"), '"');
$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.9.9.719.1.9.35.1.33.1", "-Oqv"), '"');

// EOF
