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

// ALVARION-DOT11-WLAN-MIB::brzaccVLUnitHwVersion.0 = STRING: "A"
// ALVARION-DOT11-WLAN-MIB::brzaccVLMainVersionNumber.0 = STRING: "6.6.2"
// ALVARION-DOT11-WLAN-MIB::brzaccVLUnitType.0 = INTEGER: auSA(2)
// ALVARION-DOT11-WLAN-TST-MIB::brzLighteOemProjectNameString.0 = STRING: "BreezeACCESS VL"

if (preg_match('/Alvarion\ -\ (?<hardware>[\w\ ]+),\ Version:\ (?<version>[\d\.]+)/i', $poll_device['sysDescr'], $matches))
{
  $hardware = trim($matches['hardware']);
  $version  = $matches['version'];
} else {
  $hardware = trim(snmp_get($device, "brzLighteOemProjectNameString.0", "-OQv", "ALVARION-DOT11-WLAN-TST-MIB", mib_dirs('alvarion')),'" ');
  $version  = trim(snmp_get($device, "brzaccVLMainVersionNumber.0",     "-OQv", "ALVARION-DOT11-WLAN-MIB",     mib_dirs('alvarion')),'" ');
}

$unit_type = snmp_get($device, "brzaccVLUnitType.0", "-OQv", "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));
$features  = rewrite_breeze_type($unit_type);

// EOF
