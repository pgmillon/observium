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

if (preg_match('/Allied Teles(?:is|yn) (?<hardware>\S+).* version (?<version>[\d\.\-]+) [\w\d\-]+/', $poll_device['sysDescr'], $matches))
{
  // Allied Telesis AT-8624T/2M version 2.9.1-13 11-Dec-2007
  // Allied Telesis AT-9924T-EMC version 2.9.2-08 02-Aug-2012
  // Allied Telesyn AT-8948 version 2.7.4-02 22-Aug-2005
  // Allied Telesyn AT-RP24i Rapier 24i version 2.6.1-04 09-Dec-2003
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}
else if (preg_match('/Allied Teles(?:is|yn) (?<hardware>\S+) - (?<features>.+) v(?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  // Allied Telesyn AT-9424T/SP - ATS63 v2.0.0 P_03
  // Allied Telesis AT-9424T - ATS63 v4.1.0
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
  $features = $matches['features'];
}
else if (preg_match('/^(?<hardware>AT-\S+), (?<features>.+) version (?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  // AT-8126XL, AT-S21 version 1.4.2
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
  $features = $matches['features'];
}
else if (preg_match('/^(?<hardware>AT-\S+) version (?<version>[\d\.\-]+),/', $poll_device['sysDescr'], $matches))
{
  // AT-TQ2403 version 2.1.5, Wed May 6 00:26:25 CST 2009
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}
else if (preg_match('/^(?<hardware>\S+) - Hw: \S+ - Sw: (?<version>[\d\-\._]+)/', $poll_device['sysDescr'], $matches))
{
  // iMG634A - Hw: H - Sw: 3-7_150 Copyright (c) 2005 by Allied Telesis K.K.
  // iMG624A-R2 - Hw: V1.1A - Sw: 3-8-03_14 Copyright (c) 2011 by Allied Telesis K.K.
  // iMG616SRF+ - Hw: F - Sw: 3-5_83_03_113 Copyright (c) 2005 by Allied Telesis K.K.
  // RG634A - Hw: 2A - Sw: 3-5_78 Copyright (c) 2005 by Allied Telesis K.K.
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
}
else if (preg_match('/Allied Teles(?:is|yn) .+ Switch (?<hardware>.+)/', $poll_device['sysDescr'], $matches))
{
  // Allied Telesyn Ethernet Switch AT-8012M
  // Allied Telesyn Ethernet Switch AT-8024
  $hardware = $matches['hardware'];
}
else if (preg_match('/^(?:ATI )?(?<hardware>AT-[^\s\,]+)/', $poll_device['sysDescr'], $matches))
{
  // ATI AT-8000S
  // AT-8326GB
  // AT-AR250E ADSL ROUTER
  // AT-GS950/24 Gigabit Ethernet WebSmart Switch
  $hardware = $matches['hardware'];
}

// Allied Telesis have somewhat messy MIBs. It's often hard to work out what is where. :)
if (!$hardware)
{
  // AtiSwitch-MIB::atiswitchProductType.0 = INTEGER: at8024GB(2)
  // AtiSwitch-MIB::atiswitchSw.0 = STRING: AT-S39
  // AtiSwitch-MIB::atiswitchSwVersion.0 = STRING: v3.3.0

  $hardware = snmp_get($device, "atiswitchProductType.0", "-OsvQU", "AtiSwitch-MIB");
  if ($hardware)
  {
    $version  = snmp_get($device, "atiswitchSwVersion.0", "-OsvQU", "AtiSwitch-MIB");
    $features = snmp_get($device, "atiswitchSw.0", "-OsvQU", "AtiSwitch-MIB");

    $hardware = str_replace('at', 'AT-', $hardware);
    $version  = str_replace('v', '', $version);
  } else {
    // AtiL2-MIB::atiL2SwProduct.0 = STRING: "AT-8326GB"
    // AtiL2-MIB::atiL2SwVersion.0 = STRING: "AT-S41 v1.1.6 "
    $hardware = snmp_get($device, "atiL2SwProduct.0", "-OsvQU", "AtiL2-MIB");
    if ($hardware)
    {
      $version = snmp_get($device, "atiL2SwVersion.0", "-OsvQU", "AtiL2-MIB");

      list($features, $version) = explode(' ', $version);
      $version  = str_replace('v', '', $version);
    }
  }
}
else if (!$version)
{
  // Same as above
  $version  = snmp_get($device, "atiswitchSwVersion.0", "-OsvQU", "AtiSwitch-MIB");
  if (!$version)
  {
    $version = snmp_get($device, "atiL2SwVersion.0", "-OsvQU", "AtiL2-MIB");
    list($features, $version) = explode(' ', $version);
  }
  $version  = str_replace('v', '', $version);
}

// EOF
