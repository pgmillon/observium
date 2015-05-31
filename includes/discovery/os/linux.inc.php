<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (!$os)
{
  // First check the sysObjectID, then the sysDescr
  if (strstr($sysObjectId, "1.3.6.1.4.1.8072.3.2.10")) { $os = "linux"; }
  elseif (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }

  // Specific Linux-derivatives
  if ($os == "linux")
  {
    // Check for QNAP Systems TurboNAS
    $entPhysicalMfgName = snmp_get($device, "entPhysicalMfgName.1", "-Osqnv", 'ENTITY-MIB', mib_dirs());

    // Check for devices based on Linux
    if ($sysDescr == "Open-E") { $os = "dss"; } // Checked: SysObjectId is equal to Linux, unfortunately
    elseif (snmp_get($device, "systemName.0", "-OQv", "ENGENIUS-PRIVATE-MIB") != '') { $os = "engenius"; } // Checked, also Linux
    elseif (strpos($sysDescr, "endian") !== FALSE) { $os = "endian"; }
    elseif (stripos($sysDescr, "OpenWrt") !== FALSE) { $os = "openwrt"; }
    elseif (stripos($sysDescr, "DD-WRT") !== FALSE) { $os = "ddwrt"; }
    elseif (preg_match("/Cisco Small Business/", $sysDescr)) { $os = "ciscosmblinux"; }
    elseif (strpos($entPhysicalMfgName, "QNAP") !== FALSE) { $os = "qnap"; }
    elseif (is_numeric(trim(snmp_get($device,"roomTemp.0", "-OqvU", "CAREL-ug40cdz-MIB")))) { $os = "pcoweb"; }
    elseif (strpos(trim(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv", "HOST-RESOURCES-MIB")), "syno_hw_version") !== FALSE) { $os = "dsm"; }
    elseif (strstr($sysObjectId, ".1.3.6.1.4.1.10002.1") || strpos(trim(snmp_get($device, "dot11manufacturerName.5", "-Osqnv", "IEEE802dot11-MIB", mib_dirs())), "Ubiquiti") !== FALSE)
    {
      $os = "airos";
      if (strpos(trim(snmp_get($device, "dot11manufacturerProductName.5", "-Osqnv", "IEEE802dot11-MIB", mib_dirs())), "UAP") !== FALSE) { $os = "unifi"; }
      elseif (strpos(trim(snmp_get($device, "dot11manufacturerProductName.2", "-Osqnv", "IEEE802dot11-MIB", mib_dirs())), "UAP") !== FALSE) { $os = "unifi"; }
    }
  }
  if ($os == "linux")
  {
    // Check Point SecurePlatform and GAiA
    $checkpoint_osName = snmp_get($device, ".1.3.6.1.4.1.2620.1.6.5.1.0", "-Oqv", 'CHECKPOINT-MIB', mib_dirs('checkpoint'));
    if (strpos($checkpoint_osName, "SecurePlatform") !== FALSE) { $os = "splat"; }
    elseif (strpos($checkpoint_osName, "Gaia") !== FALSE) { $os = "gaia"; }
  }

  if ($os == "linux")
  {
    // Riverbed SteelApp/Stingray appliances
    $ztm_version = snmp_get($device, ".1.3.6.1.4.1.7146.1.2.1.1.0", "-OQv", 'ZXTM-MIB', mib_dirs('riverbed'));
    if ($ztm_version != '') { $os = "zeustm"; }
  }
}

// EOF
