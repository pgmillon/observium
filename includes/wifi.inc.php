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

function discover_wifi_wlan($device_id, $wlan)
{

  $params = array('wlan_admin_status', 'wlan_beacon_period', 'wlan_bssid', 'wlan_bss_type', 'wlan_channel', 'wlan_dtim_period', 'wlan_frag_thresh',
                  'wlan_index', 'wlan_igmp_snoop', 'wlan_name', 'wlan_prot_mode', 'wlan_radio_mode', 'wlan_rts_thresh',
                  'wlan_ssid', 'wlan_ssid_bcast', 'wlan_vlan_id');

  if (is_array($GLOBALS['cache']['wifi_wlans'][$wlan['wlan_index']]))
  {
    // Database entry exists. Lets update it!
    $wlan_db = $GLOBALS['cache']['wifi_wlans'][$wlan['wlan_index']];

    $update = array();
    foreach ($params as $param)
    {
      if ($wlan[$param] != $wlan_db[$param]) { $update[$param] = $wlan[$param]; }
    }
    if (count($update))
    {
      dbUpdate($update, 'wifi_wlans', '`wlan_id` = ?', array($wlan_db['wlan_id']));
      echo('U');
    } else {
      echo('.');
    }

  } else {
    // Database entry doesn't exist. Lets create it!

    $insert = array();
    $insert['device_id'] = $device_id;
    foreach ($params as $param)
    {
      $insert[$param] = $wlan[$param];
      if ($wlan[$param] == NULL) { $insert[$param] = array('NULL'); }
    }
    $wlan_id = dbInsert($insert, 'wifi_wlans');
    echo("+");

  }

}

function discover_wifi_radio($device_id, $radio)
{
  $params  = array('radio_ap', 'radio_mib','radio_number', 'radio_type', 'radio_status', 'radio_clients', 'radio_txpower', 'radio_channel', 'radio_mac', 'radio_protection', 'radio_bsstype');

  if (is_array($GLOBALS['cache']['wifi_radios'][$radio['radio_ap']][$radio['radio_number']])) { $radio_db = $GLOBALS['cache']['wifi_radios'][$radio['radio_ap']][$radio['radio_number']]; }

  if (!isset($radio_db['wifi_radio_id']))
  {
    $insert = array();
    $insert['device_id'] = $device_id;
    foreach ($params as $param)
    {
      $insert[$param] = $radio[$param];
      if ($radio[$param] == NULL) { $insert[$param] = array('NULL'); }
    }
    $wifi_radio_id = dbInsert($insert, 'wifi_radios');
    echo("+");
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($radio[$param] != $radio_db[$param]) { $update[$param] = $radio[$param]; }
    }
    if (count($update))
    {
      dbUpdate($update, 'wifi_radios', '`wifi_radio_id` = ?', array($radio_db['wifi_radio_id']));
      echo('U');
    } else {
      echo('.');
    }
  }

  $GLOBALS['valid']['wifi']['radio'][$radio['radio_mib']][$wifi_radio_id] = 1; // FIXME. What? How it passed there?
}

// EOF
