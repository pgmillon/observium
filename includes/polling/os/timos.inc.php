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

//TIMETRA-SYSTEM-MIB::sgiSwMajorVersion.0 = Gauge32: 6
//TIMETRA-SYSTEM-MIB::sgiSwMinorVersion.0 = Gauge32: 0
//TIMETRA-SYSTEM-MIB::sgiSwVersionModifier.0 = STRING: "R6"
//TIMETRA-CHASSIS-MIB::tmnxChassisTypeName.20 = STRING: "7210 SAS-M 24F 2XFP-1"

//TiMOS-B-6.0.R6 both/mpc ALCATEL SAS-M 24F 2XFP 7210 Copyright (c) 2000-2014 Alcatel-Lucent.
//TiMOS-B-4.0.R11 both/hops ALCATEL SR 7750 Copyright (c) 2000-2007 Alcatel-Lucent.

if (preg_match('/TiMOS-B-(?P<version>[\w\.]+) .+?ALCATEL (?P<hw2>.+?) (?P<hw1>\d+ )Copyright/', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches['hw1'].$matches['hw2'];
  $version  = $matches['version'];
} else {
  // FIXME. Use snmp here, but in most cases same detected by sysDescr
}

//TIMETRA-CHASSIS-MIB::tmnxHwSerialNumber.1.50331649 = STRING: XX1416X2339
//TIMETRA-CHASSIS-MIB::tmnxHwSerialNumber.1.83886081 = STRING:
//TIMETRA-CHASSIS-MIB::tmnxHwSerialNumber.1.83886082 = STRING:
$data = array_shift(snmpwalk_cache_oid($device, 'tmnxHwSerialNumber', NULL, 'TIMETRA-CHASSIS-MIB'));
$serial = $data['tmnxHwSerialNumber']; // First element from table

unset($matches, $data);

// EOF
