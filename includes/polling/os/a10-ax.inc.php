<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// sysDescr.0 = STRING: "AX Series Advanced Traffic Manager AX1000, Advanced Core OS (ACOS) version 2.2.4-p8,"

if (preg_match('/(?<hardware>AX[0-9]+).+ version\ (?<version>[\d\.]+)/i', $poll_device['sysDescr'], $matches))
{
  $hardware = trim($matches['hardware']);
  $version  = $matches['version'];
}

// A10-AX-MIB::axSysSerialNumber.0 = STRING: "AX10A3xxxxxxxx"

$serial = snmp_get($device, "A10-AX-MIB::axSysSerialNumber.0", "-OQv", "A10-AX-MIB", mib_dirs('a10'));

// EOF