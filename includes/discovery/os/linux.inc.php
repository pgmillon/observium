<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if (!$os)
{
  // First check the sysObjectID, then the sysDescr
  if (strstr($sysObjectId, "1.3.6.1.4.1.8072.3.2.10")) { $os = "linux"; }
  elseif (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }

  // Excludes, for some old OSes previously detected as linux
  //FIXME. Remove (or comment) in r7000
  if ($os == "linux")
  {
    // steelhead: "Linux xxx 2.6.9-34.EL-rbt-16251SMP #2 SMP Wed Apr 17 23:01:13 PDT 2013 x86_64"
    // opengear:  "Linux xxx 3.4.0-uc0 #3 Mon Apr 7 02:29:20 EST 2014 armv4tl"
    foreach (array('opengear', 'steelhead') as $legacy_os)
    {
      foreach ($config['os'][$legacy_os]['sysObjectID'] as $oid)
      {
        if (strpos($sysObjectId, $oid) === 0) { unset($os); break 2; }
      }
    }
    unset($legacy_os, $oid);
    // Linux TL-WA801N 2.6.15--LSDK-7.3.0.300 #1 Mon Feb 14 14:32:06 CST 2011 mips
    // Linux TL-SG5412F 2.6.15--LSDK-6.1.1.40 #26 Fri Feb 24 16:51:49 CST 2012 mips
    if (preg_match("/^Linux TL-[WS]\w+ /", $sysDescr)) { unset($os); } // TP-LINK wireless/switch
  }

  // Specific Linux-derivatives
  if ($os == "linux")
  {
    // Check for devices based on Linux
    if ($sysDescr == "Open-E") { $os = "dss"; } // Checked: SysObjectId is equal to Linux, unfortunately
    elseif (strpos($sysDescr, "endian") !== FALSE) { $os = "endian"; }
    elseif (stripos($sysDescr, "OpenWrt") !== FALSE) { $os = "openwrt"; }
    elseif (stripos($sysDescr, "DD-WRT") !== FALSE) { $os = "ddwrt"; }
    elseif (preg_match("/Cisco Small Business/", $sysDescr)) { $os = "ciscosmblinux"; }
    // Now network based checks
    elseif (snmp_get($device, "systemName.0", "-OQv", "ENGENIUS-PRIVATE-MIB") != '') { $os = "engenius"; } // Checked, also Linux
    elseif (strpos(snmp_get($device, "entPhysicalMfgName.1", "-Osqnv", 'ENTITY-MIB', mib_dirs()), "QNAP") !== FALSE) { $os = "qnap"; }
    elseif (is_numeric(trim(snmp_get($device, "roomTemp.0", "-OqvU", "CAREL-ug40cdz-MIB")))) { $os = "pcoweb"; }
    //elseif (strpos(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv", "HOST-RESOURCES-MIB", mib_dirs()), "syno_hw_vers") !== FALSE) { $os = "dsm"; }
    elseif (is_numeric(snmp_get($device, "systemStatus.0", "-Osqnv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology')))) { $os = "dsm"; }
    elseif (strstr($sysObjectId, ".1.3.6.1.4.1.10002.1") || strpos(snmp_get($device, "dot11manufacturerName.5", "-Osqnv", "IEEE802dot11-MIB", mib_dirs()), "Ubiquiti") !== FALSE)
    {
      $os = "airos";
      if     (strpos(snmp_get($device, "dot11manufacturerProductName.5", "-Osqnv", "IEEE802dot11-MIB", mib_dirs()), "UAP") !== FALSE) { $os = "unifi"; }
      elseif (strpos(snmp_get($device, "dot11manufacturerProductName.2", "-Osqnv", "IEEE802dot11-MIB", mib_dirs()), "UAP") !== FALSE) { $os = "unifi"; }
    }
    elseif (strpos(snmp_get($device, "feHardwareModel.0", "-Oqv", "FE-FIREEYE-MIB", mib_dirs('fireeye')), "FireEye") !== FALSE) { $os = "fireeye"; }
  }
  if ($os == "linux")
  {
    // Check Point SecurePlatform and GAiA
    $checkpoint_osName = snmp_get($device, ".1.3.6.1.4.1.2620.1.6.5.1.0", "-Oqv", 'CHECKPOINT-MIB', mib_dirs('checkpoint'));
    if     (strpos($checkpoint_osName, "SecurePlatform") !== FALSE) { $os = "splat"; }
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
