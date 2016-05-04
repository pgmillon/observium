<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

unset($poll_device, $cache['devices']['uptime'][$device['device_id']]);

$snmpdata = snmp_get_multi($device, "sysUpTime.0 sysLocation.0 sysContact.0 sysName.0", "-OQUs", "SNMPv2-MIB", mib_dirs());
$polled = time();
$poll_device = $snmpdata[0];

$poll_device['sysDescr']     = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
$poll_device['sysObjectID']  = snmp_get($device, "sysObjectID.0", "-Oqvn", "SNMPv2-MIB", mib_dirs());
if (strlen($poll_device['sysObjectID']) && $poll_device['sysObjectID'][0] != '.')
{
  // Wrong Type (should be OBJECT IDENTIFIER): "1.3.6.1.4.1.25651.1.2"
  //list(, $poll_device['sysObjectID']) = explode(':', $poll_device['sysObjectID']);
  $poll_device['sysObjectID'] = '.' . $poll_device['sysObjectID'];
}
$poll_device['snmpEngineID'] = snmp_cache_snmpEngineID($device);
$poll_device['sysName'] = strtolower($poll_device['sysName']);

// Uptime data and reboot triggers

// SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
$uptimes = array('sysUpTime' => timeticks_to_sec($poll_device['sysUpTime']));

if (isset($agent_data['uptime']))
{
  list($agent_data['uptime']) = explode(' ', $agent_data['uptime']);
  $uptimes['unix-agent'] = round($agent_data['uptime']);
}
if (is_numeric($agent_data['uptime']) && $agent_data['uptime'] > 0)
{
  // Unix-agent uptime is highest priority
  $uptimes['use']     = 'unix-agent';
  $uptimes['message'] = 'Using UNIX Agent Uptime';
} else {
  if ($device['os'] != 'windows' && $device['snmp_version'] != 'v1' && is_device_mib($device, 'HOST-RESOURCES-MIB'))
  {
    // HOST-RESOURCES-MIB::hrSystemUptime.0 = Wrong Type (should be Timeticks): 1632295600
    // HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
    $hrSystemUptime = snmp_get($device, "hrSystemUptime.0", "-Oqv", "HOST-RESOURCES-MIB", mib_dirs());
    $uptimes['hrSystemUptime'] = timeticks_to_sec($hrSystemUptime);

    if (is_numeric($uptimes['hrSystemUptime']) && $uptimes['hrSystemUptime'] > 0)
    {
      // hrSystemUptime have second priority on unix systems
      $uptimes['use'] = 'hrSystemUptime';
    }
  }

  if ($uptimes['use'] != 'hrSystemUptime')
  {
    // sysUpTime used by default if all other agents data unavialable
    $uptimes['use']   = 'sysUpTime';

    // Last check snmpEngineTime
    if ($device['snmp_version'] != 'v1') // snmpEngineTime allowed only in v2c/v3
    {
      // SNMP-FRAMEWORK-MIB::snmpEngineTime.0 = INTEGER: 72393514 seconds
      $snmpEngineTime = snmp_get($device, "snmpEngineTime.0", "-OUqv", "SNMP-FRAMEWORK-MIB", mib_dirs());
    } else {
      $snmpEngineTime = 0;
    }
    if (is_numeric($snmpEngineTime) && $snmpEngineTime > 0)
    {
      if ($device['os'] == 'aos' && strlen($snmpEngineTime) > 8)
      {
        // Some Alcatel have bug with snmpEngineTime
        // See: http://jira.observium.org/browse/OBSERVIUM-763
        $snmpEngineTime = 0;
      }
      else if ($device['os'] == 'ironware')
      {
        // Check if version correct like "07.4.00fT7f3"
        $ironware_version = explode('.', $device['version']);
        if (count($ironware_version) > 2 && $ironware_version[0] > 0 && version_compare($device['version'], '5.1.0') === -1)
        {
          // IronWare before Release 05.1.00b have bug (firmware returning snmpEngineTime * 10)
          // See: http://jira.observium.org/browse/OBSERVIUM-1199
          $snmpEngineTime = $snmpEngineTime / 10;
        }
      }
      $uptimes['snmpEngineTime'] = $snmpEngineTime;

      if ($uptimes['snmpEngineTime'] > $uptimes['sysUpTime'])
      {
        $uptimes['use'] = 'snmpEngineTime';
      }
    }
  }
}

$uptimes['uptime']    = $uptimes[$uptimes['use']];        // Get actual uptime based on use flag
$uptimes['formatted'] = formatUptime($uptimes['uptime']); // Human readable uptime
if (!isset($uptimes['message'])) { $uptimes['message'] = 'Using SNMP Agent '.$uptimes['use']; }

$uptime = $uptimes['uptime'];
print_debug($uptimes['message']." ($uptime sec. => ".$uptimes['formatted'].")");

if (is_numeric($uptime) && $uptime > 0) // it really is very impossible case for $uptime equals to zero
{
  $uptimes['previous'] = $device['uptime'];              // Uptime from previous device poll
  $uptimes['diff']     = $uptimes['previous'] - $uptime; // Difference betwen previous and current uptimes

  // Notify only if current uptime less than one week (eg if changed from sysUpTime to snmpEngineTime)
  $rebooted = 0;
  if ($uptime < 604800)
  {
    if ($uptimes['diff'] > 60)
    {
      // If difference betwen old uptime ($device['uptime']) and new ($uptime)
      // greater than 60 sec, than device truly rebooted
      $rebooted = 1;
    }
    else if ($uptimes['previous'] < 300 && abs($uptimes['diff']) < 280)
    {
      // This is rare, boundary case, when device rebooted multiple times betwen polling runs
      $rebooted = 1;
    }

    // Fix reboot flag with some borderline states (roll over counters)
    if ($rebooted)
    {
      switch($uptimes['use'])
      {
        case 'hrSystemUptime':
          $uptimes['max'] = 42949673; // 497 days 2 hours 27 minutes 53 seconds, counter 2^32 (4294967296) divided by 100
          if ($device['os'] == 'windows')
          {
            // 49 days 17 hours 2 minutes 47 seconds, counter 2^32 (4294967296) divided by 1000
            $uptimes['max'] = 4294967;
          }
          break;
        case 'sysUpTime':
          $uptimes['max'] = 42949673; // 497 days 2 hours 27 minutes 53 seconds, counter 2^32 (4294967296) divided by 100
          if ($device['os'] == 'apc')
          {
            // APC hack, 49 days 17 hours 2 minutes 47 seconds, counter 2^32 (4294967296) divided by 1000
            $uptimes['max'] = 4294967;
          }
          break;
        case 'snmpEngineTime':
          $uptimes['max'] = 2147483647; // Average 68.05 years, counter is 2^32 (4294967296) divided by 2
          break;
        default:
          // By default uptime limited only by PHP max values
          // Usually int(2147483647) in 32 bit systems and int(9223372036854775807) in 64 bit systems
          $uptimes['max'] = PHP_INT_MAX;
      }
      if ($uptimes['previous'] > ($uptimes['max'] - 330) && $uptimes['previous'] < ($uptimes['max'] + 330))
      {
        // Exclude (+|- 5min 30 sec) from maximal
        $rebooted = 0;
      }
    }

    if ($rebooted)
    {
      log_event('Device rebooted: after '.formatUptime($uptimes['previous']), $device, 'device', $device['device_id'], 4);
    }
  }
  $uptimes['rebooted'] = $rebooted;

  $uptime_rrd = "uptime.rrd";

  rrdtool_create($device, $uptime_rrd, "DS:uptime:GAUGE:600:0:U ");
  rrdtool_update($device, $uptime_rrd, "N:".$uptime);

  $graphs['uptime'] = TRUE;

  print_cli_data("Uptime", $uptimes['formatted']);

  $update_array['uptime'] = $uptime;
  $cache['devices']['uptime'][$device['device_id']]['uptime']    = $uptime;
  $cache['devices']['uptime'][$device['device_id']]['sysUpTime'] = $uptimes['sysUpTime']; // Required for ports (ifLastChange)
  $cache['devices']['uptime'][$device['device_id']]['polled']    = $polled;
} else {
  print_warning("Device not have any uptime counter or uptime equals to zero.");
}
if (OBS_DEBUG) { print_vars($uptimes); }

// Rewrite sysLocation if there is a mapping array or DB override
$poll_device['sysLocation'] = snmp_fix_string($poll_device['sysLocation']);
$poll_device['sysLocation'] = rewrite_location($poll_device['sysLocation']);

$poll_device['sysContact']  = str_replace(array('\"', '"') ,"", $poll_device['sysContact']);

if ($poll_device['sysContact'] == "not set")
{
  $poll_device['sysContact'] = "";
}

// Check if snmpEngineID changed
if (strlen($poll_device['snmpEngineID'] . $device['snmpEngineID']) && $poll_device['snmpEngineID'] != $device['snmpEngineID'])
{
  $update_array['snmpEngineID'] = $poll_device['snmpEngineID'];
  if ($device['snmpEngineID'])
  {
    // snmpEngineID changed frome one to other
    log_event('snmpEngineID changed: '.$device['snmpEngineID'].' -> '.$poll_device['snmpEngineID'].' (probably the device was replaced). The device will be rediscovered.', $device, 'device', $device['device_id'], 4);
    // Reset device discover time for full re-discovery
    dbUpdate(array('last_discovered' => array('NULL')), 'devices', '`device_id` = ?', array($device['device_id']));
  } else {
    log_event("snmpEngineID -> ".$poll_device['snmpEngineID'], $device, 'device', $device['device_id']);
  }
}

$oids = array('sysObjectID', 'sysContact', 'sysName', 'sysDescr');
foreach ($oids as $oid)
{
  $poll_device[$oid] = snmp_fix_string($poll_device[$oid]);
  //print_vars($poll_device[$oid]);
  if ($poll_device[$oid] != $device[$oid])
  {
    $update_array[$oid] = ($poll_device[$oid] ? $poll_device[$oid] : array('NULL'));
    log_event("$oid -> '".$poll_device[$oid]."'", $device, 'device', $device['device_id']);
  }
}

  print_cli_data("sysObjectID",  $poll_device['sysObjectID'], 2);
  print_cli_data("snmpEngineID", $poll_device['snmpEngineID'], 2);
  print_cli_data("sysDescr",     $poll_device['sysDescr'], 2);
  print_cli_data("sysName",      $poll_device['sysName'], 2);
  print_cli_data("Location",     $poll_device['sysLocation'], 2);

$geo_detect = FALSE;
if ($device['location'] != $poll_device['sysLocation'])
{
  // Reset geolocation when location changes - triggers re-geolocation
  $geo_detect = TRUE;

  $update_array['location'] = $poll_device['sysLocation'];
  log_event("Location -> '".$poll_device['sysLocation']."'", $device, 'device', $device['device_id']);
}

if ($config['geocoding']['enable'])
{
  $db_version = get_db_version(); // Need for detect old geo DB schema

  if ($db_version < 169)
  {
    // FIXME. remove this part in r7000
    if ($geo_detect)
    {
      $update_array['location_lat'] = array('NULL');
      $update_array['location_lon'] = array('NULL');
    }
    $geo_db = array();
    foreach ($device as $k => $value)
    {
      if (strpos($k, 'location') !== FALSE)
      {
        $geo_db[$k] = $value; // GEO array for compatibility
      }
    }
  } else {
    $geo_db = dbFetchRow("SELECT * FROM `devices_locations` WHERE `device_id` = ?", array($device['device_id']));
    if (OBS_DEBUG > 1 && count($geo_db)) { print_vars($geo_db); }
  }
  $geo_db['hostname'] = $device['hostname']; // Hostname required for detect by DNS

  $geo_updated = $config['time']['now'] - strtotime($geo_db['location_updated']);
  $geo_frequency = 86400;
  if (!(is_numeric($geo_db['location_lat']) && is_numeric($geo_db['location_lon'])))
  {
    // Redetect geolocation if coordinates still empty, no more frequently than once a day
    $geo_detect = $geo_detect || ($geo_updated > $geo_frequency);
  }

  $geo_detect = $geo_detect || ($poll_device['sysLocation'] && $device['location'] != $poll_device['sysLocation']); // sysLocation cnanged
  $geo_detect = $geo_detect || ($geo_db['location_geoapi'] != strtolower($config['geocoding']['api']));             // Geo API changed
  $geo_detect = $geo_detect || ($geo_db['location_manual'] && (!$geo_db['location_country'] || $geo_db['location_country'] == 'Unknown')); // Manual coordinates passed
  $dns_only   = !$geo_detect && ($config['geocoding']['dns'] && ($geo_updated > $geo_frequency));
  $geo_detect = $geo_detect || $dns_only;                                                                           // if DNS LOC enabled, check every 1 day

  if ($geo_detect)
  {
    $update_geo = get_geolocation($poll_device['sysLocation'], $geo_db, $dns_only);
    if ($update_geo)
    {
      if (OBS_DEBUG && count($update_geo)) { print_vars($update_geo); }
      if (is_numeric($update_geo['location_lat']) && is_numeric($update_geo['location_lon']) && $update_geo['location_country'] != 'Unknown')
      {
        $geo_msg  = 'Geolocation ('.strtoupper($update_geo['location_geoapi']).') -> ';
        $geo_msg .= '['.sprintf('%f', $update_geo['location_lat']) .', ' .sprintf('%f', $update_geo['location_lon']) .'] ';
        $geo_msg .= country_from_code($update_geo['location_country']).' (Country), '.$update_geo['location_state'].' (State), ';
        $geo_msg .= $update_geo['location_county'] .' (County), ' .$update_geo['location_city'] .' (City)';
      } else {
        $geo_msg  = FALSE;
      }
      if ($db_version < 169)
      {
        // FIXME. remove this part in r7000
        $update_array = array_merge($update_array, $update_geo);
        log_event("Geolocation -> $geo_msg", $device, 'device', $device['device_id']);
      } else {
        if (is_numeric($geo_db['location_id']))
        {
          foreach ($update_geo as $k => $value)
          {
            if ($geo_db[$k] == $value) { unset($update_geo[$k]); }
          }
          if ($update_geo)
          {
            dbUpdate($update_geo, 'devices_locations', '`location_id` = ?', array($geo_db['location_id']));
            if ($geo_msg) { log_event($geo_msg, $device, 'device', $device['device_id']); }
          } // else not changed
        } else {
          $update_geo['device_id'] = $device['device_id'];
          dbInsert($update_geo, 'devices_locations');
          if ($geo_msg) { log_event($geo_msg, $device, 'device', $device['device_id']); }
        }
      }
    }
    else if (is_numeric($geo_db['location_id']))
    {
      $update_geo = array('location_updated' => format_unixtime($config['time']['now'], 'Y-m-d G:i:s')); // Increase updated time
      dbUpdate($update_geo, 'devices_locations', '`location_id` = ?', array($geo_db['location_id']));
    } # end if $update_geo
  }
}

unset($geo_detect, $geo_db, $update_geo);

// EOF
