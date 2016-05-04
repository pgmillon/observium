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

// sysDescr.0 = STRING: Hardware: x86 Family 6 Model 1 Stepping 9 AT/AT COMPATIBLE  - Software: Windows NT Version 4.0  (Build Number: 1381 Multiprocessor Free )
// sysDescr.0 = STRING: Hardware: x86 Family 6 Model 3 Stepping 4 AT/AT COMPATIBLE  - Software: Windows NT Version 3.51  (Build Number: 1057 Multiprocessor Free )
// sysDescr.0 = STRING: Hardware: x86 Family 16 Model 4 Stepping 2 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.1 (Build 2600 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: x86 Family 15 Model 2 Stepping 5 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.0 (Build 2195 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 2 Stepping 3 AT/AT COMPATIBLE - Software: Windows Version 6.0 (Build 6002 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: EM64T Family 6 Model 26 Stepping 5 AT/AT COMPATIBLE - Software: Windows Version 5.2 (Build 3790 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: Intel64 Family 6 Model 23 Stepping 6 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 8 Stepping 0 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: Intel64 Family 6 Model 44 Stepping 2 AT/AT COMPATIBLE - Software: Windows Version 6.2 (Build 9200 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: Intel64 Family 6 Model 44 Stepping 2 AT/AT COMPATIBLE - Software: Windows Version 6.3 (Build 9600 Multiprocessor Free)
// sysDescr.0 = STRING: Microsoft Windows CE Version 5.0 (Build 1400)
// sysDescr.0 = STRING: Microsoft Windows CE Version 6.0 (Build 0)

if (strstr($poll_device['sysDescr'], "x86"))     { $hardware = "Generic x86"; }
if (strstr($poll_device['sysDescr'], "ia64"))    { $hardware = "Intel Itanium IA64"; }
if (strstr($poll_device['sysDescr'], "EM64"))    { $hardware = "Intel x64"; }
if (strstr($poll_device['sysDescr'], "AMD64"))   { $hardware = "AMD x64"; }
if (strstr($poll_device['sysDescr'], "Intel64")) { $hardware = "Intel x64"; }

if (preg_match('/Version ([\d\.]+) +\(Build (?:Number: )?(\d+)/', $poll_device['sysDescr'], $matches))
{
  $windows['version'] = $matches[1];
  $windows['build'] = $matches[2];
}
if ($poll_device['sysObjectID'] == ".1.3.6.1.4.1.311.1.1.3.1.1") // Workstation
{
  switch ($windows['version'])
  {
    case '3.1':
    case '3.5':
    case '3.51':
    case '4.0':
      $icon = 'windows_old'; $version = 'NT '.$windows['version'].' Workstation';
      break;
    case '5.0':
      $icon = 'windows_old'; $version = '2000 (NT 5.0)';
      break;
    case '5.1':
      $icon = 'windows_old'; $version = 'XP (NT 5.1)';
      break;
    case '5.2':
      $icon = 'windows_old'; $version = 'XP x64 (NT 5.2)';
      break;
    case '6.0':
      if      ($windows['build'] == '6001') { $windows['sp'] = 'SP1 '; }
      else if ($windows['build'] == '6002') { $windows['sp'] = 'SP2 '; }
      else if ($windows['build'] > '6002')  { $windows['sp'] = 'SP3 '; }
      $icon = 'windows_old'; $version = 'Vista '.$windows['sp'].'(NT 6.0)';
      break;
    case '6.1':
      if      ($windows['build'] == '7601') { $windows['sp'] = 'SP1 '; }
      else if ($windows['build'] >  '7601') { $windows['sp'] = 'SP2 '; }
      $icon = 'windows_old'; $version = '7 '.$windows['sp'].'(NT 6.1)';
      break;
    case '6.2':
      $version = '8 (NT 6.2)';
      break;
    case '6.3':
      if ($windows['build'] <= 9600)
      {
        if ($windows['build'] >  '9200') { $windows['sp'] = ', Update 1'; }
        $version = '8.1'.$windows['sp'].' (NT 6.3)';
      } else {
        $version = '10 (NT '.$windows['version'].')';
        $icon = 'windows10';
      }
      break;
    default:
      $icon = 'windows_old'; $version = 'NT '.$windows['version'].' Workstation';
  }
  $windows['type'] = "workstation";
}
else if ($poll_device['sysObjectID'] == ".1.3.6.1.4.1.311.1.1.3.1.2" || // Server
         $poll_device['sysObjectID'] == ".1.3.6.1.4.1.311.1.1.3.1.3")   // Datacentre Server
{
  $windows['subtype'] = ($poll_device['sysObjectID'] == ".1.3.6.1.4.1.311.1.1.3.1.3") ? 'Datacenter ' : '';
  switch ($windows['version'])
  {
    case '3.1':
    case '3.5':
    case '3.51':
    case '4.0':
      $icon = 'windows_old'; $version = 'NT '.$windows['subtype'].'Server '.$windows['version'];
      break;
    case '5.0':
      $icon = 'windows_old'; $version = '2000 '.$windows['subtype'].'Server (NT 5.0)';
      break;
    case '5.2':
      $icon = 'windows2003'; $version = 'Server 2003 '.$windows['subtype'].'(NT 5.2)';
      break;
    case '6.0':
      if      ($windows['build'] == '6001') { $windows['sp'] = ''; }
      else if ($windows['build'] == '6002') { $windows['sp'] = 'SP2 '; }
      else if ($windows['build'] > '6002')  { $windows['sp'] = 'SP3 '; }
      $icon = 'windows_old'; $version = 'Server 2008 '.$windows['subtype'].$windows['sp'].'(NT 6.0)';
      break;
    case '6.1':
      if      ($windows['build'] == '7601') { $windows['sp'] = 'SP1 '; }
      else if ($windows['build'] >  '7601') { $windows['sp'] = 'SP2 '; }
      $icon = 'windows_old'; $version = 'Server 2008 '.$windows['subtype'].'R2 '.$windows['sp'].'(NT 6.1)';
      break;
    case '6.2':
      $version = 'Server 2012 '.$windows['subtype'].'(NT 6.2)';
      break;
    case '6.3':
      if ($windows['build'] <= 9600)
      {
        if ($windows['build'] >  '9200') { $windows['sp'] = ', Update 1'; }
        $version = 'Server 2012 '.$windows['subtype'].'R2'.$windows['sp'].' (NT 6.3)';
      } else {
        $version = 'Server 10 '.$windows['subtype'].'(NT '.$windows['version'].')'; // FIXME, currently unknown name
        $icon = 'windows10';
      }
      break;
    default:
        $icon = 'windows_old'; $version = 'NT '.$windows['subtype'].'Server '.$windows['version'];
  }
  $windows['type'] = "server";
}
else if ($poll_device['sysObjectID'] == ".1.3.6.1.4.1.311.1.1.3.3") // Windows CE
{
  $icon = 'windows_old'; $version = 'CE '.$windows['version'];
  $windows['type'] = "workstation";
}

if (isset($windows['type']))
{
  $type = $windows['type'];
}

if (strstr($poll_device['sysDescr'], "Uniprocessor"))   { $features = "Uniprocessor"; }
if (strstr($poll_device['sysDescr'], "Multiprocessor")) { $features = "Multiprocessor"; }

// Detect processor type? : I.E.  x86 Family 15 Model 2 Stepping 7

// Detect Dell hardware via OpenManage SNMP
$hw = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.9.1", "-Oqv", "MIB-Dell-10892", mib_dirs('dell'));
$hw = trim(str_replace("\"", "", $hw));
if ($hw)
{
  $hardware = "Dell " . $hw;
  $serial = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.11.1", "-Oqv", "MIB-Dell-10892", mib_dirs('dell'));
  $serial = trim(str_replace("\"", "", $serial));
}

unset($windows, $hw);

// EOF
