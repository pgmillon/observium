<?php

/* Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

unset($poll_device, $cache['devices']['uptime'][$device['device_id']]);

$snmpdata = snmp_get_multi($device, "sysUpTime.0 sysLocation.0 sysContact.0 sysName.0", "-OQUs", "SNMPv2-MIB", mib_dirs());
$polled = time();
$poll_device = $snmpdata[0];

$poll_device['sysDescr']     = snmp_get($device, "sysDescr.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
$poll_device['sysObjectID']  = snmp_get($device, "sysObjectID.0", "-Oqvn", "SNMPv2-MIB", mib_dirs());
if (strpos($poll_device['sysObjectID'], 'Wrong Type') !== FALSE)
{
  // Wrong Type (should be OBJECT IDENTIFIER): "1.3.6.1.4.1.25651.1.2"
  list(, $poll_device['sysObjectID']) = explode(':', $poll_device['sysObjectID']);
  $poll_device['sysObjectID'] = '.'.trim($poll_device['sysObjectID'], ' ."');
}
$poll_device['snmpEngineID'] = snmp_cache_snmpEngineID($device);
$poll_device['sysName'] = strtolower($poll_device['sysName']);

if (isset($agent_data['uptime'])) { list($agent_data['uptime']) = explode(' ', $agent_data['uptime']); }
if (is_numeric($agent_data['uptime']))
{
  $uptime = round($agent_data['uptime']);
  $uptime_msg = "Using UNIX Agent Uptime";
} else  {
  $hrSystemUptime = snmp_get($device, "hrSystemUptime.0", "-Oqv", "HOST-RESOURCES-MIB", mib_dirs());
  if (!empty($hrSystemUptime) && strpos($hrSystemUptime, "No") === FALSE && ($device['os'] != "windows"))
  {
    $agent_uptime = $uptime; // Move uptime into agent_uptime
    $polled = time();

    // Some Unixes return hrSystemUptime as an integer count of ten millisecond ticks instead of the
    // as a Timetick type
    if (strstr($hrSystemUptime, 'Wrong Type'))
    {
      // HOST-RESOURCES-MIB::hrSystemUptime.0 = Wrong Type (should be Timeticks): 1632295600
      list($type_msg,$ten_ms) = explode(":", $hrSystemUptime);
      $uptime = $ten_ms/100;
      echo("Found Wrong type: interpreting as seconds instead of timeticks ($uptime seconds)\n");
      $uptime_msg = "Using integer SNMP Agent hrSystemUptime";
    } else {
      // HOST-RESOURCES-MIB::hrSystemUptime.0 = Timeticks: (63050465) 7 days, 7:08:24.65
      $uptime = timeticks_to_sec($hrSystemUptime);
      $uptime_msg = "Using SNMP Agent hrSystemUptime";
    }

  } else {
    // SNMPv2-MIB::sysUpTime.0 = Timeticks: (2542831) 7:03:48.31
    $uptime = timeticks_to_sec($poll_device['sysUpTime']);
    $uptime_msg = "Using SNMP Agent sysUpTime";

    // Last check snmpEngineTime and fix if needed uptime (sysUpTime 68 year rollover issue)
    // SNMP-FRAMEWORK-MIB::snmpEngineTime.0 = INTEGER: 72393514 seconds
    $snmpEngineTime = snmp_get($device, "snmpEngineTime.0", "-OUqv", "SNMP-FRAMEWORK-MIB", mib_dirs());
    if ($device['os'] == 'aos' && strlen($snmpEngineTime) > 8)
    {
      // Some Alcatel have bug with snmpEngineTime
      // http://jira.observium.org/browse/OBSERVIUM-763
      $snmpEngineTime = 0;
    }
    else if (is_numeric($snmpEngineTime) && $snmpEngineTime > 0 && $snmpEngineTime > $uptime)
    {
      $polled = time();
      $uptime = $snmpEngineTime;
      $uptime_msg = "Using SNMP Agent snmpEngineTime";
    }
  }
}
print_debug("$uptime_msg ($uptime seconds)");

if (is_numeric($uptime))
{
  // Notify only if current uptime less than one month (eg if changed from sysUpTime to snmpEngineTime)
  if ($uptime < $device['uptime'] && $uptime < 2628000)
  {
    log_event('Device rebooted: after '.formatUptime($device['uptime']), $device, 'system', $device['uptime']);
  }

  $uptime_rrd = "uptime.rrd";

  rrdtool_create($device, $uptime_rrd, "DS:uptime:GAUGE:600:0:U ");
  rrdtool_update($device, $uptime_rrd, "N:".$uptime);

  $graphs['uptime'] = TRUE;

  print_message("Uptime: ".formatUptime($uptime));

  $update_array['uptime'] = $uptime;
  $cache['devices']['uptime'][$device['device_id']]['uptime'] = $uptime;
  $cache['devices']['uptime'][$device['device_id']]['polled'] = $polled;
}

$poll_device['sysLocation'] = str_replace(array('\"', '"'), "", $poll_device['sysLocation']);

// Rewrite sysLocation if there is a mapping array (database too?)
if (!empty($poll_device['sysLocation']))
{
  $poll_device['sysLocation'] = rewrite_location($poll_device['sysLocation']);
}

$poll_device['sysContact']  = str_replace(array('\"', '"') ,"", $poll_device['sysContact']);

if ($poll_device['sysLocation'] == "not set")
{
  $poll_device['sysLocation'] = "";
}

if ($poll_device['sysContact'] == "not set")
{
  $poll_device['sysContact'] = "";
}

// Check if snmpEngineID changed
if ($poll_device['snmpEngineID'] && $poll_device['snmpEngineID'] != $device['snmpEngineID'])
{
  $update_array['snmpEngineID'] = $poll_device['snmpEngineID'];
  if ($device['snmpEngineID'])
  {
    // snmpEngineID changed frome one to other
    log_event('snmpEngineID changed: '.$device['snmpEngineID'].' -> '.$poll_device['snmpEngineID'].' (probably the device was replaced). The device will be rediscovered.', $device, 'system');
    // Reset device discover time for full re-discovery
    dbUpdate(array('last_discovered' => array('NULL')), 'devices', '`device_id` = ?', array($device['device_id']));
  } else {
    log_event("snmpEngineID -> ".$poll_device['snmpEngineID'], $device, 'system');
  }
}

$oids = array('sysObjectID', 'sysContact', 'sysName', 'sysDescr');
foreach ($oids as $oid)
{
  if ($poll_device[$oid] && $poll_device[$oid] != $device[$oid])
  {
    $update_array[$oid] = $poll_device[$oid];
    log_event("$oid -> ".$poll_device[$oid], $device, 'system');
  }
}

// Allow override of sysLocation.

if ($attribs['override_sysLocation_bool'])
{
  $poll_device['sysLocation'] = $attribs['override_sysLocation_string'];
}

if ($poll_device['sysLocation'] && $device['location'] != $poll_device['sysLocation'])
{
  // Reset geolocation when location changes - triggers re-geolocation
  $update_array['location_lat'] = array(NULL);
  $update_array['location_lon'] = array(NULL);

  $update_array['location'] = $poll_device['sysLocation'];
  log_event("Location -> ".$poll_device['sysLocation'], $device, 'system');
}

if ($config['geocoding']['enable'])
{
  if (($poll_device['sysLocation'] && $device['location'] != $poll_device['sysLocation']) ||
      !$device['location_lat'] || !$device['location_lon'] ||
      ($device['location_geoapi'] != strtolower($config['geocoding']['api'])))
  {
    $update_array = array_merge($update_array, get_geolocation($poll_device['sysLocation'], $device['hostname']));
  }
}

// EOF
