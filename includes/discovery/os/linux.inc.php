<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (!$os)
{
  // First check the sysObjectID, then the sysDescr
  if (strstr($sysObjectId, "1.3.6.1.4.1.8072.3.2.10")) { $os = "linux"; }
  else if (preg_match("/^Linux/", $sysDescr)) { $os = "linux"; }

  // Excludes, for some old OSes previously detected as linux
  if ($os == "linux")
  {
    // steelhead: "Linux xxx 2.6.9-34.EL-rbt-16251SMP #2 SMP Wed Apr 17 23:01:13 PDT 2013 x86_64"
    // opengear:  "Linux xxx 3.4.0-uc0 #3 Mon Apr 7 02:29:20 EST 2014 armv4tl"
    // epmp:      "Linux 1658614--MELISSA-ALLISON 2.6.32.27 #2 Thu Oct 30 21:26:10 EET 2014 mips"
    foreach (array('opengear', 'steelhead', 'epmp') as $legacy_os)
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
    // Linux, Cisco Small Business WAP4410N-A, Version 2.0.6.1
    // Linux, Cisco Systems, Inc WAP371 (WAP371-E-K9), Version 1.2.0.2
    else if (preg_match("/^Linux(?: \d[\w\.\-]+)?, Cisco /", $sysDescr)) { unset($os); } // Cisco SMB
  }

  // Specific Linux-derivatives
  if ($os == "linux")
  {
    // Check for devices based on Linux by simple sysDescr parse
    if ($sysDescr == "Open-E") { $os = "dss"; } // Checked: SysObjectId is equal to Linux, unfortunately
    elseif (stripos($sysDescr, "endian") !== FALSE)   { $os = "endian"; }
    elseif (stripos($sysDescr, "OpenWrt") !== FALSE) { $os = "openwrt"; }
    elseif (stripos($sysDescr, "DD-WRT") !== FALSE)  { $os = "ddwrt"; }
    else if (preg_match("/^Linux [\w\.\:]+ \d[\.\d]+-\d[\.\d]+\.g\w{7}(?:\.rb\d+)?-smp(?:64)? #/", $sysDescr))
    {
      $os = "sophos"; // Sophos -chune
    }
  }

  if ($os == "linux")
  {
    // Now network based checks
    if     (snmp_get($device, "systemName.0", "-OQv", "ENGENIUS-PRIVATE-MIB") != '') { $os = "engenius"; } // Checked, also Linux
    elseif (strpos(snmp_get($device, "entPhysicalMfgName.1", "-Osqnv", 'ENTITY-MIB', mib_dirs()), "QNAP") !== FALSE) { $os = "qnap"; }
    elseif (is_numeric(trim(snmp_get($device, "roomTemp.0", "-OqvU", "CAREL-ug40cdz-MIB")))) { $os = "pcoweb"; }
    elseif (is_numeric(snmp_get($device, "systemStatus.0", "-Osqnv", "SYNOLOGY-SYSTEM-MIB", mib_dirs('synology')))) { $os = "dsm"; }
    elseif (strpos(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv", "HOST-RESOURCES-MIB", mib_dirs()), "syno_hw_vers") !== FALSE) { $os = "dsm"; } // Old Synology not support SYNOLOGY-SYSTEM-MIB
    elseif (strstr($sysObjectId, ".1.3.6.1.4.1.10002.1") || strpos(snmp_get($device, "dot11manufacturerName.5", "-Osqnv", "IEEE802dot11-MIB", mib_dirs()), "Ubiquiti") !== FALSE)
    {
      $os = "airos";
      $data = snmpwalk_cache_oid($device, "dot11manufacturerProductName", array(), "IEEE802dot11-MIB", mib_dirs());
      if ($data)
      {
        $data = current($data);
        if (strpos($data['dot11manufacturerProductName'], "UAP") !== FALSE) { $os = "unifi"; }
      }
    }
    elseif (strpos(snmp_get($device, "feHardwareModel.0", "-Oqv", "FE-FIREEYE-MIB", mib_dirs('fireeye')), "FireEye") !== FALSE) { $os = "fireeye"; }
  }

  if ($os == "linux")
  {
    // Check DD-WRT/OpenWrt, since it changed sysDescr, but still use dd-wrt/openwrt in sysName
    $sysName = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
    if     (stripos($sysName, "dd-wrt")  !== FALSE) { $os = "ddwrt"; }
    elseif (stripos($sysName, "openwrt") !== FALSE) { $os = "openwrt"; }
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

  if ($os == "linux")
  {
    if (snmp_get($device, "nasMgrSoftwareVersion.0", "-OQv", 'READYNAS-MIB', mib_dirs('netgear')) != '') { $os = "netgear-readynas"; }
  }

  if ($os == "linux")
  {
    if (snmp_get($device, "systemName.0", "-Osqnv", 'SFA-INFO', mib_dirs('ddn')) != '') { $os = "ddn"; }
  }
}

// EOF
