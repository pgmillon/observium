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

//  Polling of wireless sessions data for Juniper Wireless (ex Trapeze)
//
//  TRAPEZE-NETWORKS-CLIENT-SESSION-MIB

echo(" TRAPEZE-NETWORKS-CLIENT-SESSION-MIB ");

// Cache DB entries
$sessions_db = dbFetchRows("SELECT `mac_addr` FROM `wifi_sessions` WHERE `device_id` = ?", array($device['device_id']));
foreach ($sessions_db as $session_db)
{
  $clean_mac = str_replace(array(':', '-'), '', $session_db['mac_addr']);
  $sessions_db[$clean_mac] = $session_db;
}
if (OBS_DEBUG && count($sessions_db)) { print_vars($sessions_db); }

$radios_db = dbFetchRows("SELECT `wifi_radio_id`, `radio_number`,`ap_number`, `wifi_accesspoints`.`device_id` FROM `wifi_accesspoints`, `wifi_radios` WHERE `wifi_radios`.`radio_ap` = wifi_accesspoints.`wifi_accesspoint_id` AND wifi_accesspoints.`device_id` = ?", array($device['device_id']));
foreach ($radios_db as $radio_db)
{
  $radios_sorted_db[$radio_db['ap_number']][$radio_db['radio_number']] = $radio_db;
}

$sessions_array = snmpwalk_cache_multi_oid($device, "trpzClSessClientSessionTable", $sessions_array, "TRAPEZE-NETWORKS-CLIENT-SESSION-MIB", mib_dirs('trapeze'), OBS_SNMP_ALL_NUMERIC);
if (OBS_DEBUG > 1 && count($sessions_array)) { print_vars($sessions_array); }

$timestamp = date('Y-m-d H:i:s', strtotime("now"));
// Goes through the SNMP sessions data
foreach ($sessions_array as $index => $session)
{
  list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(".", $index);
  $clean_mac = zeropad(dechex($a_a)).zeropad(dechex($a_b)).zeropad(dechex($a_c)).zeropad(dechex($a_d)).zeropad(dechex($a_e)).zeropad(dechex($a_f));

  // Mapping OIDs<>DB
  $db_oids = array('trpzClSessClientSessSessionId' => 'session_id',
                   'trpzClSessClientSessUsername' => 'username',
                   'trpzClSessClientSessIpAddress' => 'ipv4_addr',
                   'trpzClSessClientSessSsid' => 'ssid',
                   'trpzClSessClientSessSessionState' => 'state');

  $new_index = $clean_mac;

  foreach ($db_oids as $db_oid => $db_value)
  {
    $db_insert[$db_value] = $session[$db_oid];
  }
  $db_insert['device_id'] = $device['device_id'];
  $db_insert['mac_addr']  = $clean_mac;
  $db_insert['uptime']    = timeticks_to_sec($session['trpzClSessClientSessTimeStamp']); // FIXME. There timestamp, not timetick!
  $db_insert['timestamp'] = $timestamp;
  if ($session['trpzClSessClientSessRadioNum'] == "radio-1")      { $radio_number = '1'; }
  else if ($session['trpzClSessClientSessradioNum'] == "radio-2") { $radio_number = '2'; }

  $db_insert['radio_id'] = $radios_sorted_db[$session['trpzClSessClientSessApNum']][$radio_number]['wifi_radio_id'];
  if (OBS_DEBUG > 1) { print_vars($db_insert); }
  if (!is_array($sessions_db[$new_index])) //If new session
  {
    $session_id = dbInsert($db_insert, 'wifi_sessions');
    echo('+');
  }
  else if (array_diff($db_insert, $sessions_db[$new_index]))
  {
    $updated = dbUpdate($db_insert, 'wifi_sessions', '`mac_addr` = ? AND `device_id` = ?', array($new_index, $device['device_id']));
    echo("U");
  } else {
    echo(".");
  }

// XXX can add trending of link quality and bandwidth per MAC addr with trpzClSessClientSessionStatisticsTable
// XXX No delete so we can see when a user was connected the last time, might be interesting to have a "deleted" boolean to know if he is still online
}

///FIXME. Clean/delete old sessions from DB

unset($oids, $oid, $sessions_array, $sessions_db,$db_insert);

// EOF
