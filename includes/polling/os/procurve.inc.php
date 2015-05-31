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

list($hardware, $version, ) = explode(",", str_replace(", ", ",", $poll_device['sysDescr']));

// Clean up hardware
$hardware = str_replace("PROCURVE", "ProCurve", $hardware);
if (substr($hardware,0,3) == "HP ") { $hardware = substr($hardware,3); }
if (substr($hardware,0,24) == "Hewlett-Packard Company ") { $hardware = substr($hardware,24); }

$altversion = trim(snmp_get($device,"hpSwitchOsVersion.0", "-Oqv", "NETSWITCH-MIB", mib_dirs('hp')), '"');
if ($altversion) { $version = $altversion; }

$altversion = trim(snmp_get($device,"snAgImgVer.0", "-Oqv", "HP-SN-AGENT-MIB", mib_dirs('hp')), '"');
if ($altversion) { $version = $altversion; }

if (preg_match('/^PROCURVE (.*) - (.*)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = "ProCurve " . $regexp_result[1];
  $version = $regexp_result[2];
}

$serial = trim(snmp_get($device, "hpHttpMgSerialNumber.0", "-Oqv", "SEMI-MIB", mib_dirs('hp')), '"');

// EOF
