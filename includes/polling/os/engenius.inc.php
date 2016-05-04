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

///////////// Senao Access Points (tested with ECB-9500)

// Yes, that's the Kenel version.
$kenelversion = trim(snmp_get($device, "entKenelVersion.0", "-OQv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao')),'" ');

if ($kenelversion)
{
  // Only fetch app version when we found a Kenel Version.
  $appversion = trim(snmp_get($device, "entAppVersion.0", "-OQv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao')),'" ');
  $version = "Kernel $kenelversion / Apps $appversion";
}

$serial = trim(snmp_get($device, "entSN.0", "-OQv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao')),'" ');

$hwversion = trim(snmp_get($device, "entHwVersion.0", "-OQv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao')),'" .');

// There doesn't seem to be a real hardware identification.. sysName will have to do?
// On Engenius APs this is changeable in the system properties!
$hardware = str_replace("EnGenius ","",snmp_get($device,"sysName.0", "-OQv")) . ($hwversion == '' ? '' : " v" . $hwversion);
if ($hardware[0] != 'E') { $hardware = ''; } // If the user has changed sysName, don't use it as hardware. Silly check, will work in 99% of cases?

// Operational mode
$mode = snmp_get($device, "entSysMode.0", "-OQv", "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB", mib_dirs('senao'));
switch ($mode)
{
  case 'ap-router':
    $features = "Router mode";
    break;
  case 'repeater':
    $features = "Universal repeater mode";
    break;
  case 'ap-bridge':
    $features = "Access Point mode";
    break;
  case 'client-bridge':
    $features = "Client Bridge mode";
    break;
  case 'client-router':
    $features = "Client router mode";
    break;
  case 'wds-bridge':
    $features = "WDS Bridge mode";
    break;
  default:
    $features = '';
    break;
}

///////////// Engenius Access Points (tested with ECB-350)

if ($version == '')
{
  $version = snmp_get($device, "modelName.0", "-OQv", "ENGENIUS-PRIVATE-MIB");
}

// Unfortunately, no operational mode or even a serial number available in the Engenius MIBs. :(

// EOF
