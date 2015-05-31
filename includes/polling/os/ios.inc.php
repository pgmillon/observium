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

## Check Cisco configuration age

echo("Cisco configuration ages\n\n");
$oids = "sysUpTime.0 ccmHistoryRunningLastChanged.0 ccmHistoryRunningLastSaved.0 ccmHistoryStartupLastChanged.0";
$data = snmp_get_multi($device, $oids, "-OQUst", "SNMPv2-MIB:CISCO-CONFIG-MAN-MIB", mib_dirs(array("cisco")));
$config_age = $data[0];

foreach ($config_age as $key => $val)
{
  $config_age[$key] = $val/100;
}

$RunningLastChanged = $config_age['sysUpTime'] - $config_age['ccmHistoryRunningLastChanged'];
$RunningLastChangedTS = time() - $RunningLastChanged;
$RunningLastSaved = $config_age['sysUpTime'] - $config_age['ccmHistoryRunningLastSaved'];
$RunningLastSavedTS = time() - $RunningLastSaved;
$StartupLastChanged = $config_age['sysUpTime'] - $config_age['ccmHistoryStartupLastChanged'];
$StartupLastChangedTS = time() - $StartupLastChanged;

$sysUptimeTS = time() - $config_age['sysUpTime'];

echo('sysUptime : '.format_unixtime($sysUptimeTS).' | '.formatUptime($config_age['sysUpTime']).PHP_EOL);
echo('Running   : '.format_unixtime($RunningLastChangedTS).' | '.formatUptime($RunningLastChanged).PHP_EOL);
echo('Saved     : '.format_unixtime($RunningLastSavedTS)  .' | '.formatUptime($RunningLastSaved).PHP_EOL);
echo('Startup   : '.format_unixtime($StartupLastChangedTS).' | '.formatUptime($StartupLastChanged).PHP_EOL);

# 7200 and IOS-XE (ASR1k)
if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $poll_device['sysDescr'], $regexp_result))
{
  $features = $regexp_result[1];
  $version = $regexp_result[2];
}

# 7600
elseif (preg_match('/Cisco Internetwork Operating System Software\s+IOS \(tm\) [^ ]+ Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $poll_device['sysDescr'], $regexp_result))
{
  $features = $regexp_result[1];
  $version = $regexp_result[2];
}

# If we have not managed to match any IOS string yet (and that would be surprising)
# we can try to poll the Entity Mib to see what's inside
elseif (is_array($entPhysical))
{
  if ($entPhysical['entPhysicalContainedIn'] === '0')
  {
    if (!empty($entPhysical['entPhysicalSoftwareRev']))
    {
      $version = $entPhysical['entPhysicalSoftwareRev'];
    }
    if (!empty($entPhysical['entPhysicalModelName']))
    {
      $hardware = $entPhysical['entPhysicalModelName'];
    } else {
      $hardware = $entPhysical['entPhysicalName'];
    }
  }
}

if (empty($hardware)) { $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB"); }

if (stristr($hardware, 'AIRAP') || substr($hardware,0,4) == 'AIR-') { $ios_type = 'wireless'; }

// Set type to a predefined type for the OS if it's not already set
if (isset($ios_type) && $device['type'] != $ios_type)
{
  $update_array['type'] = $ios_type;
  log_event("type -> ".$ios_type, $device, 'system');
}
unset($ios_type);

// Disable max-rep for 2960S and other stacked switches (causes a heavy load)
if ('cat29xxStack' == $hardware)
{
  unset($config['os'][$device['os']]['snmp']['max-rep']);
}

// EOF
