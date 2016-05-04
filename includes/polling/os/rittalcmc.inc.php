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

if (preg_match('/^Rittal (?<hardware>.+) SN (?<serial>\d+) HW V(?<rev>[\d\.\-_]+) - SW V(?<version>[\d\.\-_]+)/', $poll_device['sysDescr'], $matches))
{
  // Rittal CMC III PU SN 40341455 HW V3.00 - SW V3.13.00_2
  $hardware = $matches['hardware'];
  $serial   = $matches['serial'];
  $version  = $matches['version'];
}

//$version = snmp_get($device, "cmcIIIUnitOSRev.0", "-OQv", "RITTAL-CMC-III-MIB").' FW '.snmp_get($device, "cmcIIIUnitFWRev.0", "-OQv", "RITTAL-CMC-III-MIB");
//$serial  = snmp_get($device, "cmcIIIUnitSerial.0", "-OQv", "RITTAL-CMC-III-MIB");

// EOF
