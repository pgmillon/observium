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

// Discovery of Juniper Wireless (ex Trapeze) APs and radios
//
// TRAPEZE-NETWORKS-AP-CONFIG-MIB

echo(" TRAPEZE-NETWORKS-AP-CONFIG-MIB ");

// Getting APs

$accesspoints_snmp = snmpwalk_cache_multi_oid($device, "trpzApConfApConfigTable", $accesspoints_snmp, "TRAPEZE-NETWORKS-AP-CONFIG-MIB", mib_dirs('trapeze'),TRUE);
if ($debug) { print_vars($accesspoints_snmp); }

$accesspoints_db = dbFetchRows("SELECT `name`, `model`, `location`, `fingerprint`, `serial`, `device_id`, `ap_number` FROM `wifi_accesspoints` WHERE `device_id` = ?", array($device['device_id']));

foreach ($accesspoints_db as $accesspoint_db)
{
  $ap_db[$accesspoint_db['ap_number']] = $accesspoint_db;
}

// Mapping OIDs<>DB
$db_oids = array('trpzApConfApConfigRemoteSiteName' => 'location',
                 'trpzApConfApConfigApName'         => 'name',
                 'trpzApConfApConfigApModelName'    => 'model',
                 'trpzApConfApConfigFingerprint'    => 'fingerprint',
                 'trpzApConfApConfigApSerialNum'    => 'serial');

// Goes through the SNMP APs data
foreach ($accesspoints_snmp as $ap_number => $accesspoint_snmp)
{
  foreach ($db_oids as $db_oid => $db_value)
  {
    $db_insert[$db_value] = $accesspoint_snmp[$db_oid];
  } // DB: wifi_accesspoint_id, device_id, number, name, serial, model, location, fingerprint, delete
  $db_insert['device_id'] = $device['device_id'];
  $db_insert['ap_number'] = $ap_number;
  if ($debug && count($db_insert)) { print_vars($db_insert); }
  if (!is_array($ap_db[$ap_number]))
  {
    $accesspoint_id = dbInsert($db_insert, 'wifi_accesspoints');
    echo('+');
  }
  else if (array_diff($ap_db[$ap_number], $db_insert))
  {
    if ($debug) { print_vars(array_diff($ap_db[$new_index],$db_insert));}
    $updated = dbUpdate($db_insert, 'wifi_accesspoints', '`ap_number` = ? AND `device_id` = ?', array($ap_number, $device['device_id']));
    echo("U");
  }
  else
  {
    echo(".");
  }
}

unset($accesspoints_db, $accesspoints_snmp, $ap_db, $db_insert);

// Getting Radios

$radios_snmp = snmpwalk_cache_twopart_oid($device, "trpzApConfRadioConfigTable", $radios_snmp, "TRAPEZE-NETWORKS-AP-CONFIG-MIB", mib_dirs('trapeze'));
if ($debug) { print_vars($radios_snmp); }

$accesspoints_db = dbFetchRows("SELECT `wifi_accesspoint_id`, `ap_number` FROM `wifi_accesspoints` WHERE `device_id` = ?", array($device['device_id']));

foreach ($accesspoints_db as $accesspoint_db)
{
  $ap_db[$accesspoint_db['ap_number']] = $accesspoint_db;
}

$radios_db = dbFetchRows("SELECT `wifi_radio_id`, `mac_addr`, `radio_number`,`ap_number`, `type`,`radio_status`,`numasoclients`,`txpow`,`channel`,`accesspoint_id` FROM `wifi_accesspoints`, `wifi_radios` WHERE wifi_radios.`accesspoint_id` = wifi_accesspoints.`wifi_accesspoint_id` AND wifi_accesspoints.`device_id` = ?", array($device['device_id']));

// Mapping OIDs<>DB
$db_oids = array('trpzApStatRadioStatusMacRadioNum' => 'radio_number',
                 'trpzApConfRadioConfigRadioType'   => 'type',
                 'trpzApConfRadioConfigRadioMode'   => 'radio_status',
                 'trpzApConfRadioConfigTxPower'     => 'txpow',
                 'trpzApConfRadioConfigChannel'     => 'channel');

foreach ($radios_db as $radio_db)
{
  $radios_sorted_db[$radio_db['ap_number']][$radio_db['radio_number']] = $radio_db;
}

// Goes through the SNMP radio data
foreach ($radios_snmp as $ap_number => $ap_radios)
{
  $accesspoint_id = $ap_db[$ap_number]['wifi_accesspoint_id'];

  foreach ($ap_radios as $radio_number => $radio)
  {
    foreach ($db_oids as $db_oid => $db_value)
    {
      $db_insert[$db_value] = $radio[$db_oid];
    }
    $db_insert['radio_number'] = $radio_number;
    $db_insert['accesspoint_id'] = $accesspoint_id;
    if ($debug) { print_vars($db_insert); }
    if (!is_array($radios_sorted_db[$ap_number][$radio_number]))
    {
      $wifi_radio_id = dbInsert($db_insert, 'wifi_radios');
      echo('+');
    }
    else if (array_diff($radios_sorted_db[$ap_number][$radio_number],$db_insert))
    {
      if ($debug) {  print_vars(array_diff($radios_sorted_db[$ap_number][$radio_number],$db_insert));}
      $updated = dbUpdate($db_insert, 'wifi_radios', '`wifi_radio_id` = ?', array($radios_sorted_db[$ap_number][$radio_number]['wifi_radio_id']));
        echo("U");
    }
    else
    {
      echo(".");
    }
  }
}

unset($radios_db, $radios_snmp, $radios_sorted_db, $db_insert);

// EOF
