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

if (is_device_mib($device, 'CISCO-CONFIG-MAN-MIB'))
{
  // Check Cisco configuration age

  $oids = "sysUpTime.0 ccmHistoryRunningLastChanged.0 ccmHistoryRunningLastSaved.0 ccmHistoryStartupLastChanged.0";
  $data = snmp_get_multi($device, $oids, "-OQUst", "SNMPv2-MIB:CISCO-CONFIG-MAN-MIB", mib_dirs(array("cisco")));
  $config_age = $data[0];

  foreach ($config_age as $key => $val)
  {
    $config_age[$key] = $val/100;
  }

  $RunningLastChanged   = $config_age['sysUpTime'] - $config_age['ccmHistoryRunningLastChanged'];
  $RunningLastChangedTS = time() - $RunningLastChanged;
  $RunningLastSaved     = $config_age['sysUpTime'] - $config_age['ccmHistoryRunningLastSaved'];
  $RunningLastSavedTS   = time() - $RunningLastSaved;
  $StartupLastChanged   = $config_age['sysUpTime'] - $config_age['ccmHistoryStartupLastChanged'];
  $StartupLastChangedTS = time() - $StartupLastChanged;

  $sysUptimeTS = time() - $config_age['sysUpTime'];

  $os_additional_info["Cisco configuration ages"] = array(
    'sysUptime' => format_unixtime($sysUptimeTS)         .' | '.formatUptime($config_age['sysUpTime']),
    'Running'   => format_unixtime($RunningLastChangedTS).' | '.formatUptime($RunningLastChanged),
    'Saved'     => format_unixtime($RunningLastSavedTS)  .' | '.formatUptime($RunningLastSaved),
    'Startup'   => format_unixtime($StartupLastChangedTS).' | '.formatUptime($StartupLastChanged),
  );
}

$sysDescr = preg_replace("/\s+/", " ", $poll_device['sysDescr']); // Replace all spaces and newline to single space
// Generic IOS/IOS-XE/IES/IOS-XR sysDescr
if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([\w\d]+)-\w\),.+?Version ([^, ]+)/', $sysDescr, $matches))
{
  //Cisco IOS Software, Catalyst 4500 L3 Switch Software (cat4500e-ENTSERVICESK9-M), Version 15.2(1)E3, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2014 by Cisco Systems, Inc. Compiled Mon 05-May-14 07:56 b
  //Cisco IOS Software, IOS-XE Software (PPC_LINUX_IOSD-IPBASEK9-M), Version 15.2(2)S, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2012 by Cisco Systems, Inc. Compiled Mon 26-Mar-12 15:23 by mcpre
  //Cisco IOS Software, IES Software (IES-LANBASEK9-M), Version 12.2(52)SE1, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2010 by Cisco Systems, Inc. Compiled Tue 09-Feb-10 03:17 by prod_rel_team
  $features = $matches[1];
  $version  = $matches[2];
}
else if (preg_match('/^Cisco Internetwork Operating System Software IOS \(tm\) [^ ]+ Software \([^\-]+-([\w\d]+)-\w\),.+?Version ([^, ]+)/', $sysDescr, $matches))
{
  //Cisco Internetwork Operating System Software IOS (tm) 7200 Software (UBR7200-IK8SU2-M), Version 12.3(17b)BC8, RELEASE SOFTWARE (fc1) Technical Support: http://www.cisco.com/techsupport Copyright (c) 1986-2007 by cisco Systems, Inc. Compiled Fri 29-Ju
  //Cisco Internetwork Operating System Software IOS (tm) C1700 Software (C1700-Y-M), Version 12.2(4)YA2, EARLY DEPLOYMENT RELEASE SOFTWARE (fc1) Synched to technology version 12.2(5.4)T TAC Support: http://www.cisco.com/tac Copyright (c) 1986-2002 by ci
  $features = $matches[1];
  $version  = $matches[2];
}
else if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\), Version ([^\[]+)\[([^\]]+)\]/', $sysDescr, $matches))
{
  //Cisco IOS XR Software (Cisco 12816/PRP), Version 4.3.2[Default] Copyright (c) 2014 by Cisco Systems, Inc.
  //Cisco IOS XR Software (Cisco 12404/PRP), Version 3.6.0[00] Copyright (c) 2007 by Cisco Systems, Inc.
  //Cisco IOS XR Software (Cisco ASR9K Series), Version 5.1.2[Default] Copyright (c) 2014 by Cisco Systems, Inc.
  //$hardware = $matches[1];
  $features = $matches[3];
  $version  = $matches[2];
}
else if (preg_match('/^Cisco NX-OS\(tm\) (?<hw1>\w+), Software \((?<hw2>.+?)\),.+?Version (?<version>[^, ]+)/', $sysDescr, $matches))
{
  //Cisco NX-OS(tm) n7000, Software (n7000-s2-dk9), Version 6.2(8b), RELEASE SOFTWARE Copyright (c) 2002-2013 by Cisco Systems, Inc.
  //Cisco NX-OS(tm) n1000v, Software (n1000v-dk9), Version 5.2(1)SV3(1.2), RELEASE SOFTWARE Copyright (c) 2002-2011 by Cisco Systems, Inc. Device Manager Version nms.sro not found,  Compiled 11/11/2014 15:00:00
  //Cisco NX-OS(tm) n5000, Software (n5000-uk9), Version 6.0(2)N2(7), RELEASE SOFTWARE Copyright (c) 2002-2012 by Cisco Systems, Inc. Device Manager Version 6.2(1),  Compiled 4/28/2015 5:00:00
  //Cisco NX-OS(tm) n7000, Software (n7000-s2-dk9), Version 6.2(8a), RELEASE SOFTWARE Copyright (c) 2002-2013 by Cisco Systems, Inc. Compiled 5/15/2014 20:00:00
  //Cisco NX-OS(tm) n3000, Software (n3000-uk9), Version 6.0(2)U2(2), RELEASE SOFTWARE Copyright (c) 2002-2012 by Cisco Systems, Inc. Device Manager Version nms.sro not found, Compiled 2/12/2014 8:00:00
  list(, $features) = explode('-', $matches['hw2'], 2);
  $version  = $matches['version'];
}

// All other Cisco devices
if (is_array($entPhysical))
{
  if ($config['discovery_modules']['inventory'])
  {
    if ($entPhysical['entPhysicalClass'] == 'stack')
    {
      // If it's stacked device try get chassis instead
      $chassis = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` = ? AND `entPhysicalContainedIn` = ?', array($device['device_id'], 'chassis', '1'));
      if ($chassis['entPhysicalModelName'])
      {
        $entPhysical = $chassis;
      }
    }
    else if (empty($entPhysical['entPhysicalModelName']) || $entPhysical['entPhysicalModelName'] == 'MIDPLANE')
    {
      // F.u. Cisco.. for some platforms (4948/4900M) they store correct model and version not in chassis
      $hw_module = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` = ? AND `entPhysicalContainedIn` = ?', array($device['device_id'], 'module', '2'));
      if ($hw_module['entPhysicalModelName'])
      {
        $entPhysical = $hw_module;
      }
    }
    else if (empty($entPhysical['entPhysicalSoftwareRev']))
    {
      // 720X, try again get correct serial/version
      $hw_module = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` = ? AND `entPhysicalContainedIn` = ? AND `entPhysicalSerialNum` != ?', array($device['device_id'], 'module', '1', ''));
      if ($hw_module['entPhysicalSoftwareRev'])
      {
        if ($device['os'] == 'iosxe')
        {
          // For IOS-XE fix only version
          $entPhysical['entPhysicalSoftwareRev'] = $hw_module['entPhysicalSoftwareRev'];
        } else {
          $entPhysical = $hw_module;
        }
      }
    }
  }

  if ($entPhysical['entPhysicalContainedIn'] === '0' || $entPhysical['entPhysicalContainedIn'] === '1' || $entPhysical['entPhysicalContainedIn'] === '2')
  {
    if ((empty($version) || $device['os'] == 'iosxe') && !empty($entPhysical['entPhysicalSoftwareRev']))
    {
      $version = $entPhysical['entPhysicalSoftwareRev'];
    }
    if (!empty($entPhysical['entPhysicalModelName']))
    {
      if (preg_match('/ (rev|dev)/', $entPhysical['entPhysicalModelName']))
      {
        // F.u. Cisco again.. again..
        // i.e.: entPhysicalModelName = "73-7036-1 rev 80 dev 0",
        //       entPhysicalDescr     = "12404/PRP chassis, Hw Serial#: TBA07510208, Hw Revision: 0x00"
      } else {
        $hardware = $entPhysical['entPhysicalModelName'];
      }
    } else {
      $hardware = $entPhysical['entPhysicalName'];
    }
    if (!empty($entPhysical['entPhysicalSerialNum']))
    {
      $serial = $entPhysical['entPhysicalSerialNum'];
    }
  }
}

// NOTE. In CISCO-PRODUCTS-MIB uses weird hardware names (entPhysicalName uses human readable names)
// Examples:
// sysObjectID [.1.3.6.1.4.1.9.1.658]: cisco7604
// entPhysicalModelName:               CISCO7604
// sysObjectID [.1.3.6.1.4.1.9.1.927]: cat296048TCS
// entPhysicalModelName:               WS-C2960-48TC-S
// sysObjectID [.1.3.6.1.4.1.9.1.1208]: cat29xxStack
// entPhysicalModelName:               WS-C2960S-F48TS-L
if (empty($hardware) && $poll_device['sysObjectID'])
{
  // Try translate instead duplicate get sysObjectID
  $hardware = snmp_translate($poll_device['sysObjectID'], "SNMPv2-MIB:CISCO-PRODUCTS-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs(array("cisco")));
}
if (empty($hardware))
{
  // If translate false, try get sysObjectID again
  $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs(array("cisco")));
}

// Additional checks for IOS devices
if ($device['os'] == 'ios')
{
  if (stristr($hardware, 'AIRAP') || substr($hardware,0,4) == 'AIR-') { $ios_type = 'wireless'; }

  // Set type to a predefined type for the OS if it's not already set
  if (isset($ios_type) && $device['type'] != $ios_type)
  {
    $type = $ios_type;
  }
  unset($ios_type);

  // Disable max-rep for 2960S and other stacked switches (causes a heavy load)
  if ($hardware == 'cat29xxStack' || strpos($hardware, 'C2960S'))
  {
    unset($config['os'][$device['os']]['snmp']['max-rep']);
  }
}

unset($chassis, $model);

// EOF
