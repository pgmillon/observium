<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 *   These functions perform rewrites on strings and numbers.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Process strings to give them a nicer capitalisation format
 *
 * This function does rewrites from the lowercase identifiers we use to the
 * standard capitalisation. UK English style plurals, please.
 * This uses $config['nicecase']
 *
 * @param string $item
 * @return string
*/
function nicecase($item)
{
  $mappings = $GLOBALS['config']['nicecase'];
  if (isset($mappings[$item])) { return $mappings[$item]; }
  //$item = preg_replace('/([a-z])([A-Z]{2,})/', '$1 $2', $item); // turn "fixedAC" into "fixed AC"

  return ucfirst($item);
}

/**
 * Trim string and remove paired and/or escaped quotes from string
 *
 * @param string $string Input string
 * @return string Cleaned string
 */
function trim_quotes($string, $flags = OBS_QUOTES_TRIM)
{
  $string = trim($string); // basic trim of string
  if (strpos($string, '"') !== FALSE && is_flag_set(OBS_QUOTES_TRIM, $flags))
  {
    if (strpos($string, '\"') !== FALSE)
    {
      $string = str_replace('\"', '"', $string); // replace escaped quotes
    }
    $quotes = array('["\']', // remove single quotes
                    //'\\\"',  // remove escaped quotes
                    );
    foreach ($quotes as $quote)
    {
      $pattern = '/^(' . $quote . ')(?<value>.*?)(\1)$/s';
      while (preg_match($pattern, $string, $matches))
      {
        $string = $matches['value'];
      }
    }
  }
  return $string;
}

 /**
  * Humanize User
  *
  *   Process an array containing user info to add/modify elements.
  *
  * @param array $user
  */
// TESTME needs unit testing
function humanize_user(&$user)
{
  krsort($GLOBALS['config']['user_level']); // Order levels from max to low
  foreach ($GLOBALS['config']['user_level'] as $level => $entry)
  {
    if ($user['level'] >= $level)
    {
      $user['permission']  = $entry['permission'];
      $user['icon']        = $entry['icon'];
      $user['row_class']   = $entry['row_class'];
      $user['subtext']     = $entry['subtext'];
      $user['level_label'] = $entry['name'];
      $user['level_name']  = $entry['name'];
      $user['level_real']  = $level;
      break;
    }
  }
}

/**
 * Humanize Scheduled Maintanance
 *
 *   Process an array containing a row from `alert_maintenance` and in-place add/modify elements for use in the UI
 *
 *
 */
function humanize_maintenance(&$maint)
{

  $maint['duration'] = $maint['maint_end'] - $maint['maint_start'];

  if ($maint['maint_global'] == 1)
  {
    $maint['entities_text'] = '<span class="label label-info">Global Maintenance</span>';
  } else {
    $entities = dbFetchRows("SELECT * FROM `alerts_maint_assoc` WHERE `maint_id` = ?", array($maint['maint_id']));
    if (count($entities))
    {
      foreach ($entities as $entity)
      {

      }
    } else {
      $maint['entities_text'] = '<span class="label label-error">Maintenance is not associated with any entities.</span>';
    }
  }

  $maint['row_class'] = '';

  if ($maint['maint_start'] > $GLOBALS['config']['time']['now'])
  {
    $maint['start_text'] = "+".formatUptime($maint['maint_start'] - $GLOBALS['config']['time']['now']);
  } else {
    $maint['start_text'] = "-".formatUptime($GLOBALS['config']['time']['now'] - $maint['maint_start']);
    $maint['row_class']  = "warning";
    $maint['active_text'] = '<span class="label label-warning pull-right">active</span>';
  }

  if ($maint['maint_end'] > $GLOBALS['config']['time']['now'])
  {
    $maint['end_text'] = "+".formatUptime($maint['maint_end'] - $GLOBALS['config']['time']['now']);
  } else {
    $maint['end_text'] = "-".formatUptime($GLOBALS['config']['time']['now'] - $maint['maint_end']);
    $maint['row_class']  = "disabled";
    $maint['active_text'] = '<span class="label label-disabled pull-right">ended</span>';
  }

}

/**
 * Humanize Alert Check
 *
 *   Process an array containing a row from `alert_checks` and in place to add/modify elements.
 *
 * @param array $alert_check
 */
// TESTME needs unit testing
function humanize_alert_check(&$check)
{
  // Fetch the queries to build the alert table.
  list($query, $param, $query_count) = build_alert_table_query(array('alert_test_id' => $check['alert_test_id']));

  // Fetch a quick set of alert_status values to build the alert check status text
  $query = str_replace(" * ", " `alert_status` ", $query);
  $check['entities'] = dbFetchRows($query, $param);

  $check['entity_status'] = array('up' => 0, 'down' => 0, 'unknown' => 0, 'delay' => 0, 'suppress' => 0);
  foreach ($check['entities'] as $alert_table_id => $alert_table_entry)
  {
    if ($alert_table_entry['alert_status'] == '1')      { ++$check['entity_status']['up'];
    } elseif($alert_table_entry['alert_status'] == '0') { ++$check['entity_status']['down'];
    } elseif($alert_table_entry['alert_status'] == '2') { ++$check['entity_status']['delay'];
    } elseif($alert_table_entry['alert_status'] == '3') { ++$check['entity_status']['suppress'];
    } else                                              { ++$check['entity_status']['unknown']; }
  }

  $check['num_entities'] = count($check['entities']);

  if ($check['entity_status']['up'] == $check['num_entities'])
  {
    $check['class']  = "green"; $check['html_row_class'] = "up";
  } elseif($check['entity_status']['down'] > '0') {
    $check['class']  = "red"; $check['html_row_class'] = "error";
  } elseif($check['entity_status']['delay'] > '0') {
    $check['class']  = "orange"; $check['html_row_class'] = "warning";
  } elseif($check['entity_status']['suppress'] > '0') {
    $check['class']  = "purple"; $check['html_row_class'] = "suppressed";
  } elseif($check['entity_status']['up'] > '0') {
    $check['class']  = "green"; $check['html_row_class'] = "success";
  } else {
    $check['entity_status']['class']  = "gray"; $check['table_tab_colour'] = "#555555"; $check['html_row_class'] = "disabled";
  }

  $check['status_numbers'] = '<span class="green">'. $check['entity_status']['up']. '</span>/<span class="purple">'. $check['entity_status']['suppress'].
         '</span>/<span class=red>'. $check['entity_status']['down']. '</span>/<span class=orange>'. $check['entity_status']['delay'].
         '</span>/<span class=gray>'. $check['entity_status']['unknown']. '</span>';

  // We return nothing, $check is modified in place.
}

 /**
  * Humanize Alert
  *
  *   Process an array containing a row from `alert_entry` and `alert_entry-state` in place to add/modify elements.
  *
  * @param array $alert_entry
  */
// TESTME needs unit testing
function humanize_alert_entry(&$entry)
{
  // Exit if already humanized
  if ($entry['humanized']) { return; }

  // Set colours and classes based on the status of the alert
  if ($entry['alert_status'] == '1')
  {
    // 1 means ok. Set blue text and disable row class
    $entry['class']  = "green"; $entry['html_row_class'] = "up"; $entry['status'] = "OK";
  } elseif($entry['alert_status'] == '0') {
    // 0 means down. Set red text and error class
    $entry['class']  = "red"; $entry['html_row_class'] = "error"; $entry['status'] = "FAILED";
  } elseif($entry['alert_status'] == '2') {
    // 2 means the checks failed but we're waiting for x repetitions. set colour to orange and class to warning
    $entry['class']  = "orange"; $entry['html_row_class'] = "warning"; $entry['status'] = "DELAYED";
  } elseif($entry['alert_status'] == '3') {
    // 3 means the checks failed but the alert is suppressed. set the colour to purple and the row class to suppressed
    $entry['class']  = "purple"; $entry['html_row_class'] = "suppressed"; $entry['status'] = "SUPPRESSED";
  } else {
    // Anything else set the colour to grey and the class to disabled.
    $entry['class']  = "gray"; $entry['html_row_class'] = "disabled"; $entry['status'] = "Unknown";
  }

  // Set the checked/changed/alerted entries to formatted date strings if they exist, else set them to never
  if (!isset($entry['last_checked']) || $entry['last_checked'] == '0') { $entry['checked'] = "<i>Never</i>"; } else { $entry['checked'] = formatUptime(time()-$entry['last_checked'], 'short-3'); }
  if (!isset($entry['last_changed']) || $entry['last_changed'] == '0') { $entry['changed'] = "<i>Never</i>"; } else { $entry['changed'] = formatUptime(time()-$entry['last_changed'], 'short-3'); }
  if (!isset($entry['last_alerted']) || $entry['last_alerted'] == '0') { $entry['alerted'] = "<i>Never</i>"; } else { $entry['alerted'] = formatUptime(time()-$entry['last_alerted'], 'short-3'); }
  if (!isset($entry['last_recovered']) || $entry['last_recovered'] == '0') { $entry['recovered'] = "<i>Never</i>"; } else { $entry['recovered'] = formatUptime(time()-$entry['last_recovered'], 'short-3'); }

  if (!isset($entry['ignore_until']) || $entry['ignore_until'] == '0') { $entry['ignore_until_text'] = "<i>Disabled</i>"; } else { $entry['ignore_until_text'] = format_timestamp($entry['ignore_until']); }
  if (!isset($entry['ignore_until_ok']) || $entry['ignore_until_ok'] == '0') { $entry['ignore_until_ok_text'] = "<i>Disabled</i>"; } else { $entry['ignore_until_ok_text'] = '<span class="purple">Yes</span>'; }

  // Set humanized so we can check for it later.
  $entry['humanized'] = TRUE;

  // We return nothing as we're working on a reference.
}

/**
 * Humanize Device
 *
 *   Process an array containing a row from `devices` to add/modify elements.
 *
 * @param array $device
 * @return none
 */
// TESTME needs unit testing
function humanize_device(&$device)
{
  global $config;

  // Exit if already humanized
  if ($device['humanized']) { return; }

  // Expand the device state array from the php serialized string
  $device['state'] = unserialize($device['device_state']);

  // Set the HTML class and Tab color for the device based on status
  if ($device['status'] == '0')
  {
    $device['html_row_class'] = "error";
  } else {
    $device['html_row_class'] = "up";  // Fucking dull gay colour, but at least there's a semicolon now - tom
                                            // Your mum's a semicolon - adama
  }
  if ($device['ignore'] == '1')
  {
    $device['html_row_class'] = "suppressed";
    if ($device['status'] == '1')
    {
      $device['html_row_class'] = "success";  // Why green for ignore? Confusing!
                                              // I chose this purely because using green for up and blue for up/ignore was uglier.
    }
  }
  if ($device['disabled'] == '1')
  {
    $device['html_row_class'] = "disabled";
  }

  // Set country code always lowercase
  if (isset($device['location_country']))
  {
    $device['location_country'] = strtolower($device['location_country']);
  }

  // Set the name we print for the OS
  $device['os_text'] = $config['os'][$device['os']]['text'];

  // Mark this device as being humanized
  $device['humanized'] = TRUE;
}

/**
 * Humanize BGP Peer
 *
 * Returns a the $peer array with processed information:
 * row_class, table_tab_colour, state_class, admin_class
 *
 * @param array $peer
 * @return array $peer
 *
 */
// TESTME needs unit testing
function humanize_bgp(&$peer)
{
  // Exit if already humanized
  if ($peer['humanized']) { return; }

  // Set colours and classes based on the status of the peer
  if ($peer['bgpPeerAdminStatus'] == 'stop' || $peer['bgpPeerAdminStatus'] == 'halted')
  {
    // Peer is disabled, set row to warning and text classes to muted.
    $peer['html_row_class'] = "warning";
    $peer['state_class']    = "muted";
    $peer['admin_class']    = "muted";
    $peer['alert']          = 0;
    $peer['disabled']       = 1;
  }
  else if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running" )
  {
    // Peer is enabled, set state green and check other things
    $peer['admin_class'] = "success";
    if ($peer['bgpPeerState'] == "established")
    {
      // Peer is up, set colour to blue and disable row class
      $peer['state_class'] = "success"; $peer['html_row_class'] = "up";
    } else {
      // Peer is down, set colour to red and row class to error.
      $peer['state_class'] = "danger"; $peer['html_row_class'] = "error";
    }
  }

  // Set text and colour if peer is same AS, private AS or external.
  if ($peer['bgpPeerRemoteAs'] == $peer['bgpLocalAs'])                                  { $peer['peer_type_class'] = "info";    $peer['peer_type'] = "iBGP"; }
  else if ($peer['bgpPeerRemoteAS'] >= '64512' && $peer['bgpPeerRemoteAS'] <= '65535')  { $peer['peer_type_class'] = "warning"; $peer['peer_type'] = "Priv eBGP"; }
  else                                                                                  { $peer['peer_type_class'] = "danger";  $peer['peer_type'] = "eBGP"; }

  // Format (compress) the local/remote IPs if they're IPv6
  $peer['human_localip']  = (strstr($peer['bgpPeerLocalAddr'],  ':')) ? Net_IPv6::compress($peer['bgpPeerLocalAddr'])  : $peer['bgpPeerLocalAddr'];
  $peer['human_remoteip'] = (strstr($peer['bgpPeerRemoteAddr'], ':')) ? Net_IPv6::compress($peer['bgpPeerRemoteAddr']) : $peer['bgpPeerRemoteAddr'];

  // Set humanized entry in the array so we can tell later
  $peer['humanized'] = TRUE;
}

function process_port_label(&$this_port, $device)
{
  global $config;

  // OS Specific rewrites (get your shit together, vendors)
  if ($device['os'] == 'zxr10') { $this_port['ifAlias'] = preg_replace("/^" . str_replace("/", "\\/", $this_port['ifName']) . "\s*/", '', $this_port['ifDescr']); }
  if ($device['os'] == 'ciscosb' && $this_port['ifType'] == 'propVirtual' && is_numeric($this_port['ifDescr'])) {  $this_port['ifName'] = 'Vl'.$this_port['ifDescr']; }

  $this_port['ifAlias'] = snmp_fix_string($this_port['ifAlias']); // Fix ord chars

  // Process ifDescr/ifName/ifAlias if needed
  //$oids = array('ifDescr', 'ifAlias', 'ifName'); // FIXME, required examples.. currently used only for ifDescr
  $oids = array('ifDescr');
  foreach ($oids as $oid)
  {
    if (isset($config['os'][$device['os']][$oid]))
    {
      foreach ($config['os'][$device['os']][$oid] as $pattern)
      {
        if (preg_match($pattern, $this_port[$oid], $matches))
        {
          print_debug("Port oid '$oid' rewritten: '" . $this_port[$oid] . "' -> '" . $matches[1] . "'");
          $this_port[$oid] = $matches[1];
          break;
        }
      }
    }
  }

  // Added for Brocade NOS. Will copy ifDescr -> ifAlias if ifDescr != ifName
  if ($config['os'][$device['os']]['ifDescr_ifAlias'] && $this_port['ifDescr'] != $this_port['ifName'])
  {
    $this_port['ifAlias'] = $this_port['ifDescr'];
  }

  // Here definition override for ifDescr, because Calix switch ifDescr <> ifName since fw 2.2
  // Note, only for 'calix' os now
  if ($device['os'] == 'calix')
  {
    unset($config['os'][$device['os']]['ifname']);
    $version_parts = explode('.', $device['version']);
    if ($version_parts[0] > 2 || ($version_parts[0] == 2 && $version_parts[1] > 1))
    {
      if ($this_port['ifName'] == '')
      {
        $this_port['port_label'] = $this_port['ifDescr'];
      } else {
        $this_port['port_label'] = $this_port['ifName'];
      }
    }
  }

  if (isset($config['os'][$device['os']]['ifname']))
  {
    if ($this_port['ifName'] == '')
    {
      $this_port['port_label'] = $this_port['ifDescr'];
    } else {
      $this_port['port_label'] = $this_port['ifName'];
    }
  }
  else if (isset($config['os'][$device['os']]['ifalias']))
  {
    $this_port['port_label'] = $this_port['ifAlias'];
  } else {
    $this_port['port_label'] = $this_port['ifDescr'];
    if (isset($config['os'][$device['os']]['ifindex']))
    {
      $this_port['port_label'] .= ' ' . $this_port['ifIndex'];
    }
  }
  if ($device['os'] == "speedtouch")
  {
    list($this_port['port_label']) = explode("thomson", $this_port['port_label']);
  }

  $this_port['port_label_short'] = short_ifname($this_port['port_label']);

  return TRUE;
}

/**
 * Humanize port.
 *
 * Returns a the $port array with processed information:
 * label, humans_speed, human_type, html_class and human_mac
 * row_class, table_tab_colour
 *
 * Escaping should not be done here, since these values are used in the API too.
 *
 * @param array $port
 * @return array $port
 *
 */
// TESTME needs unit testing
function humanize_port(&$port)
{
  global $config, $cache;

  // Exit if already humanized
  if ($port['humanized']) { return; }

  // If we can get the device data from the global cache, do it, else pull it from the db (mostly for external scripts)
  if (is_array($GLOBALS['cache']['devices']['id'][$port['device_id']]))
  {
    $device = &$GLOBALS['cache']['devices']['id'][$port['device_id']];
  } else {
    $device = device_by_id_cache($port['device_id']);
  }

  // Workaround for devices/ports who long time not updated and have empty port_label
  if (empty($port['port_label']))
  {
    process_port_label($port, $device);
  }

  // Set entity variables for use by code which uses entities
  // Base label part: TenGigabitEthernet3/3 -> TenGigabitEthernet, GigabitEthernet4/8.722 -> GigabitEthernet, Vlan2603 -> Vlan
  $port['port_label_base'] = preg_replace('/^([A-Za-z ]*).*/', '$1', $port['port_label']);
  $port['port_label_num']  = substr($port['port_label'], strlen($port['port_label_base'])); // Second label part

  // Set humanised values for use in UI
  $port['human_speed'] = humanspeed($port['ifSpeed']);
  $port['human_type']  = rewrite_iftype($port['ifType']);
  $port['html_class']  = port_html_class($port['ifOperStatus'], $port['ifAdminStatus'], $port['encrypted']);
  $port['human_mac']   = format_mac($port['ifPhysAddress']);

  // Set entity_* values for code which expects them.
  $port['entity_name']      = $port['port_label'];
  $port['entity_shortname'] = $port['port_label_short'];
  $port['entity_descr']     = $port['ifAlias'];

  $port['table_tab_colour'] = "#aaaaaa"; $port['row_class'] = ""; $port['icon'] = 'port-ignored'; // Default
  $port['admin_status'] = $port['ifAdminStatus'];
  if     ($port['ifAdminStatus'] == "down")
  {
    $port['admin_status'] = 'disabled';
    $port['row_class'] = "disabled";
    $port['icon'] = 'port-disabled';
  }
  elseif ($port['ifAdminStatus'] == "up")
  {
    $port['admin_status'] = 'enabled';
    switch ($port['ifOperStatus'])
    {
      case 'up':
        $port['table_tab_colour'] = "#194B7F"; $port['row_class'] = "up";      $port['icon'] = 'port-up';
        break;
      case 'monitoring':
        // This is monitoring ([e|r]span) ports
        $port['table_tab_colour'] = "#008C00"; $port['row_class'] = "success"; $port['icon'] = 'port-up';
        break;
      case 'down':
        $port['table_tab_colour'] = "#cc0000"; $port['row_class'] = "error";   $port['icon'] = 'port-down';
        break;
      case 'lowerLayerDown':
        $port['table_tab_colour'] = "#ff6600"; $port['row_class'] = "warning"; $port['icon'] = 'port-down';
        break;
      case 'testing':
      case 'unknown':
      case 'dormant':
      case 'notPresent':
        $port['table_tab_colour'] = "#85004b"; $port['row_class'] = "info";    $port['icon'] = 'port-ignored';
        break;
    }
  }

  // If the device is down, colour the row/tab as 'warning' meaning that the entity is down because of something below it.
  if ($device['status'] == '0')
  {
    $port['table_tab_colour'] = "#ff6600"; $port['row_class'] = "warning"; $port['icon'] = 'port-ignored';
  }

  $port['in_rate'] = $port['ifInOctets_rate'] * 8;
  $port['out_rate'] = $port['ifOutOctets_rate'] * 8;

  // Colour in bps based on speed if > 50, else by UI convention.
  if ($port['ifSpeed'] > 0)
  {
    $in_perc  = round($port['in_rate']/$port['ifSpeed']*100);
    $out_perc = round($port['out_rate']/$port['ifSpeed']*100);
  } else {
    // exclude division by zero error
    $in_perc  = 0;
    $out_perc = 0;
  }
  if ($port['in_rate'] == 0)
  {
    $port['bps_in_style'] = '';
  } elseif ($in_perc < '50') {
    $port['bps_in_style'] = 'color: #008C00;';
  } else {
    $port['bps_in_style'] = 'color: ' . percent_colour($in_perc) . '; ';
  }

  // Colour out bps based on speed if > 50, else by UI convention.
  if ($port['out_rate'] == 0)
  {
    $port['bps_out_style'] = '';
  } elseif ($out_perc < '50') {
    $port['bps_out_style'] = 'color: #394182;';
  } else {
    $port['bps_out_style'] = 'color: ' . percent_colour($out_perc) . '; ';
  }

  // Colour in and out pps based on UI convention
  $port['pps_in_style'] = ($port['ifInUcastPkts_rate'] == 0) ? '' : 'color: #740074;';
  $port['pps_out_style'] = ($port['ifOutUcastPkts_rate'] == 0) ? '' : 'color: #FF7400;';

  $port['humanized'] = TRUE; /// Set this so we can check it later.

}

// Rewrite arrays

$rewrite_entSensorType = array(
  'celsius' => 'C',
  'unknown' => '',
  'specialEnum' => 'C',
  'watts' => 'W',
  'truthvalue' => '',
);

// List of real names for cisco entities
$entPhysicalVendorTypes = array(
  'cevC7xxxIo1feTxIsl'    => 'C7200-IO-FE-MII',
  'cevChassis7140Dualfe'  => 'C7140-2FE',
  'cevChassis7204'        => 'C7204',
  'cevChassis7204Vxr'     => 'C7204VXR',
  'cevChassis7206'        => 'C7206',
  'cevChassis7206Vxr'     => 'C7206VXR',
  'cevCpu7200Npe200'      => 'NPE-200',
  'cevCpu7200Npe225'      => 'NPE-225',
  'cevCpu7200Npe300'      => 'NPE-300',
  'cevCpu7200Npe400'      => 'NPE-400',
  'cevCpu7200Npeg1'       => 'NPE-G1',
  'cevCpu7200Npeg2'       => 'NPE-G2',
  'cevPa1feTxIsl'         => 'PA-FE-TX-ISL',
  'cevPa2feTxI82543'      => 'PA-2FE-TX',
  'cevPa8e'               => 'PA-8E',
  'cevPaA8tX21'           => 'PA-8T-X21',
  'cevMGBIC1000BaseLX'    => '1000BaseLX GBIC',
  'cevPort10GigBaseLR'    => '10GigBaseLR'
);

$rewrite_junos_hardware = array(
  '.1.3.6.1.4.1.4874.1.1.1.6.2' => 'E120',
  '.1.3.6.1.4.1.4874.1.1.1.6.1' => 'E320',
  '.1.3.6.1.4.1.4874.1.1.1.1.1' => 'ERX1400',
  '.1.3.6.1.4.1.4874.1.1.1.1.3' => 'ERX1440',
  '.1.3.6.1.4.1.4874.1.1.1.1.5' => 'ERX310',
  '.1.3.6.1.4.1.4874.1.1.1.1.2' => 'ERX700',
  '.1.3.6.1.4.1.4874.1.1.1.1.4' => 'ERX705',
  '.1.3.6.1.4.1.2636.1.1.1.2.43' => 'EX2200',
  '.1.3.6.1.4.1.2636.1.1.1.2.30' => 'EX3200',
  '.1.3.6.1.4.1.2636.1.1.1.2.76' => 'EX3300',
  '.1.3.6.1.4.1.2636.1.1.1.2.31' => 'EX4200',
  '.1.3.6.1.4.1.2636.1.1.1.2.44' => 'EX4500',
  '.1.3.6.1.4.1.2636.1.1.1.2.74' => 'EX6210',
  '.1.3.6.1.4.1.2636.1.1.1.2.32' => 'EX8208',
  '.1.3.6.1.4.1.2636.1.1.1.2.33' => 'EX8216',
  '.1.3.6.1.4.1.2636.1.1.1.2.16' => 'IRM',
  '.1.3.6.1.4.1.2636.1.1.1.2.13' => 'J2300',
  '.1.3.6.1.4.1.2636.1.1.1.2.23' => 'J2320',
  '.1.3.6.1.4.1.2636.1.1.1.2.24' => 'J2350',
  '.1.3.6.1.4.1.2636.1.1.1.2.14' => 'J4300',
  '.1.3.6.1.4.1.2636.1.1.1.2.22' => 'J4320',
  '.1.3.6.1.4.1.2636.1.1.1.2.19' => 'J4350',
  '.1.3.6.1.4.1.2636.1.1.1.2.15' => 'J6300',
  '.1.3.6.1.4.1.2636.1.1.1.2.20' => 'J6350',
  '.1.3.6.1.4.1.2636.1.1.1.2.38' => 'JCS1200',
  '.1.3.6.1.4.1.2636.10' => 'BX7000',
  '.1.3.6.1.4.1.12532.252.2.1' => 'SA-2000',
  '.1.3.6.1.4.1.12532.252.6.1' => 'SA-6000',
  '.1.3.6.1.4.1.4874.1.1.1.5.1' => 'UMC Sys Mgmt',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.4' => 'WXC1800',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.1' => 'WXC250',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.5' => 'WXC2600',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.6' => 'WXC3400',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.2' => 'WXC500',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.3' => 'WXC590',
  '.1.3.6.1.4.1.2636.3.41.1.1.5.7' => 'WXC7800',
  '.1.3.6.1.4.1.2636.1.1.1.2.4' => 'M10',
  '.1.3.6.1.4.1.2636.1.1.1.2.11' => 'M10i',
  '.1.3.6.1.4.1.2636.1.1.1.2.18' => 'M120',
  '.1.3.6.1.4.1.2636.1.1.1.2.3' => 'M160',
  '.1.3.6.1.4.1.2636.1.1.1.2.2' => 'M20',
  '.1.3.6.1.4.1.2636.1.1.1.2.9' => 'M320',
  '.1.3.6.1.4.1.2636.1.1.1.2.1' => 'M40',
  '.1.3.6.1.4.1.2636.1.1.1.2.8' => 'M40e',
  '.1.3.6.1.4.1.2636.1.1.1.2.5' => 'M5',
  '.1.3.6.1.4.1.2636.1.1.1.2.10' => 'M7i',
  '.1.3.6.1.4.1.2636.1.1.1.2.68' => 'MAG6610',
  '.1.3.6.1.4.1.2636.1.1.1.2.67' => 'MAG6611',
  '.1.3.6.1.4.1.2636.1.1.1.2.66' => 'MAG8600',
  '.1.3.6.1.4.1.2636.1.1.1.2.89' => 'MX10',
  '.1.3.6.1.4.1.2636.1.1.1.2.29' => 'MX240',
  '.1.3.6.1.4.1.2636.1.1.1.2.88' => 'MX40',
  '.1.3.6.1.4.1.2636.1.1.1.2.25' => 'MX480',
  '.1.3.6.1.4.1.2636.1.1.1.2.90' => 'MX5',
  '.1.3.6.1.4.1.2636.1.1.1.2.57' => 'MX80',
  '.1.3.6.1.4.1.2636.1.1.1.2.21' => 'MX960',
  '.1.3.6.1.4.1.3224.1.1' => 'Netscreen',
  '.1.3.6.1.4.1.3224.1.3' => 'Netscreen 10',
  '.1.3.6.1.4.1.3224.1.4' => 'Netscreen 100',
  '.1.3.6.1.4.1.3224.1.5' => 'Netscreen 1000',
  '.1.3.6.1.4.1.3224.1.9' => 'Netscreen 204',
  '.1.3.6.1.4.1.3224.1.10' => 'Netscreen 208',
  '.1.3.6.1.4.1.3224.1.8' => 'Netscreen 25',
  '.1.3.6.1.4.1.3224.1.2' => 'Netscreen 5',
  '.1.3.6.1.4.1.3224.1.7' => 'Netscreen 50',
  '.1.3.6.1.4.1.3224.1.6' => 'Netscreen 500',
  '.1.3.6.1.4.1.3224.1.13' => 'Netscreen 5000',
  '.1.3.6.1.4.1.3224.1.14' => 'Netscreen 5GT',
  '.1.3.6.1.4.1.3224.1.17' => 'Netscreen 5GT-ADSL-A',
  '.1.3.6.1.4.1.3224.1.23' => 'Netscreen 5GT-ADSL-A-WLAN',
  '.1.3.6.1.4.1.3224.1.19' => 'Netscreen 5GT-ADSL-B',
  '.1.3.6.1.4.1.3224.1.25' => 'Netscreen 5GT-ADSL-B-WLAN',
  '.1.3.6.1.4.1.3224.1.21' => 'Netscreen 5GT-WLAN',
  '.1.3.6.1.4.1.3224.1.12' => 'Netscreen 5XP',
  '.1.3.6.1.4.1.3224.1.11' => 'Netscreen 5XT',
  '.1.3.6.1.4.1.3224.1.15' => 'Netscreen Client',
  '.1.3.6.1.4.1.3224.1.28' => 'Netscreen ISG1000',
  '.1.3.6.1.4.1.3224.1.16' => 'Netscreen ISG2000',
  '.1.3.6.1.4.1.3224.1.52' => 'Netscreen SSG140',
  '.1.3.6.1.4.1.3224.1.53' => 'Netscreen SSG140',
  '.1.3.6.1.4.1.3224.1.35' => 'Netscreen SSG20',
  '.1.3.6.1.4.1.3224.1.36' => 'Netscreen SSG20-WLAN',
  '.1.3.6.1.4.1.3224.1.54' => 'Netscreen SSG320',
  '.1.3.6.1.4.1.3224.1.55' => 'Netscreen SSG350',
  '.1.3.6.1.4.1.3224.1.29' => 'Netscreen SSG5',
  '.1.3.6.1.4.1.3224.1.30' => 'Netscreen SSG5-ISDN',
  '.1.3.6.1.4.1.3224.1.33' => 'Netscreen SSG5-ISDN-WLAN',
  '.1.3.6.1.4.1.3224.1.31' => 'Netscreen SSG5-v92',
  '.1.3.6.1.4.1.3224.1.34' => 'Netscreen SSG5-v92-WLAN',
  '.1.3.6.1.4.1.3224.1.32' => 'Netscreen SSG5-WLAN',
  '.1.3.6.1.4.1.3224.1.50' => 'Netscreen SSG520',
  '.1.3.6.1.4.1.3224.1.18' => 'Netscreen SSG550',
  '.1.3.6.1.4.1.3224.1.51' => 'Netscreen SSG550',
  '.1.3.6.1.4.1.2636.1.1.1.2.84' => 'QFX3000',
  '.1.3.6.1.4.1.2636.1.1.1.2.85' => 'QFX5000',
  '.1.3.6.1.4.1.2636.1.1.1.2.82' => 'QFX Switch',
  '.1.3.6.1.4.1.2636.1.1.1.2.41' => 'SRX100',
  '.1.3.6.1.4.1.2636.1.1.1.2.64' => 'SRX110',
  '.1.3.6.1.4.1.2636.1.1.1.2.49' => 'SRX1400',
  '.1.3.6.1.4.1.2636.1.1.1.2.36' => 'SRX210',
  '.1.3.6.1.4.1.2636.1.1.1.2.58' => 'SRX220',
  '.1.3.6.1.4.1.2636.1.1.1.2.39' => 'SRX240',
  '.1.3.6.1.4.1.2636.1.1.1.2.35' => 'SRX3400',
  '.1.3.6.1.4.1.2636.1.1.1.2.34' => 'SRX3600',
  '.1.3.6.1.4.1.2636.1.1.1.2.86' => 'SRX550',
  '.1.3.6.1.4.1.2636.1.1.1.2.28' => 'SRX5600',
  '.1.3.6.1.4.1.2636.1.1.1.2.26' => 'SRX5800',
  '.1.3.6.1.4.1.2636.1.1.1.2.40' => 'SRX650',
  '.1.3.6.1.4.1.2636.1.1.1.2.27' => 'T1600',
  '.1.3.6.1.4.1.2636.1.1.1.2.7' => 'T320',
  '.1.3.6.1.4.1.2636.1.1.1.2.6' => 'T640',
  '.1.3.6.1.4.1.2636.1.1.1.2.17' => 'TX',
  '.1.3.6.1.4.1.2636.1.1.1.2.37' => 'TXPlus',
);

# FIXME needs a rewrite, preferrably in form above? ie cat3524tXLEn etc
$rewrite_cisco_hardware = array(
  '.1.3.6.1.4.1.9.1.275' => 'C2948G-L3',
);

$rewrite_ftos_hardware = array (
  '.1.3.6.1.4.1.6027.1.1.1'=> 'E1200',
  '.1.3.6.1.4.1.6027.1.1.2'=> 'E600',
  '.1.3.6.1.4.1.6027.1.1.3'=> 'E300',
  '.1.3.6.1.4.1.6027.1.1.4'=> 'E610',
  '.1.3.6.1.4.1.6027.1.1.5'=> 'E1200i',
  '.1.3.6.1.4.1.6027.1.2.1'=> 'C300',
  '.1.3.6.1.4.1.6027.1.2.2'=> 'C150',
  '.1.3.6.1.4.1.6027.1.3.1'=> 'S50',
  '.1.3.6.1.4.1.6027.1.3.2'=> 'S50E',
  '.1.3.6.1.4.1.6027.1.3.3'=> 'S50V',
  '.1.3.6.1.4.1.6027.1.3.4'=> 'S25P-AC',
  '.1.3.6.1.4.1.6027.1.3.5'=> 'S2410CP',
  '.1.3.6.1.4.1.6027.1.3.6'=> 'S2410P',
  '.1.3.6.1.4.1.6027.1.3.7'=> 'S50N-AC',
  '.1.3.6.1.4.1.6027.1.3.8'=> 'S50N-DC',
  '.1.3.6.1.4.1.6027.1.3.9'=> 'S25P-DC',
  '.1.3.6.1.4.1.6027.1.3.10'=> 'S25V',
  '.1.3.6.1.4.1.6027.1.3.11'=> 'S25N',
  '.1.3.6.1.4.1.6027.1.3.12'=> 'S60',
  '.1.3.6.1.4.1.6027.1.3.13'=> 'S55',
  '.1.3.6.1.4.1.6027.1.3.14'=> 'S4810',
  '.1.3.6.1.4.1.6027.1.3.15'=> 'Z9000'
);

$rewrite_fortinet_hardware = array(
  '.1.3.6.1.4.1.12356.102.1.1000' => array('name' => 'FortiAnalyzer 100'),
  '.1.3.6.1.4.1.12356.102.1.10002' => array('name' => 'FortiAnalyzer 1000B'),
  '.1.3.6.1.4.1.12356.102.1.1001' => array('name' => 'FortiAnalyzer 100A'),
  '.1.3.6.1.4.1.12356.102.1.1002' => array('name' => 'FortiAnalyzer 100B'),
  '.1.3.6.1.4.1.12356.102.1.20000' => array('name' => 'FortiAnalyzer 2000'),
  '.1.3.6.1.4.1.12356.102.1.20001' => array('name' => 'FortiAnalyzer 2000A'),
  '.1.3.6.1.4.1.12356.102.1.4000' => array('name' => 'FortiAnalyzer 400'),
  '.1.3.6.1.4.1.12356.102.1.40000' => array('name' => 'FortiAnalyzer 4000'),
  '.1.3.6.1.4.1.12356.102.1.40001' => array('name' => 'FortiAnalyzer 4000A'),
  '.1.3.6.1.4.1.12356.102.1.4002' => array('name' => 'FortiAnalyzer 400B'),
  '.1.3.6.1.4.1.12356.102.1.8000' => array('name' => 'FortiAnalyzer 800'),
  '.1.3.6.1.4.1.12356.102.1.8002' => array('name' => 'FortiAnalyzer 800B'),
'.1.3.6.1.4.1.12356.101.1.10' => array('name' => 'FortiGate ONE'),
'.1.3.6.1.4.1.12356.101.1.1000' => array('name' => 'FortiGate 100'),
'.1.3.6.1.4.1.12356.101.1.10000' => array('name' => 'FortiGate 1000'),
'.1.3.6.1.4.1.12356.101.1.10001' => array('name' => 'FortiGate 1000A'),
'.1.3.6.1.4.1.12356.101.1.10002' => array('name' => 'FortiGate 1000AFA2'),
'.1.3.6.1.4.1.12356.101.1.10003' => array('name' => 'FortiGate 1000ALENC'),
'.1.3.6.1.4.1.12356.101.1.10004' => array('name' => 'Fortigate 1000C'),
'.1.3.6.1.4.1.12356.101.1.1001' => array('name' => 'FortiGate 100A'),
'.1.3.6.1.4.1.12356.101.1.1002' => array('name' => 'FortiGate 110C'),
'.1.3.6.1.4.1.12356.101.1.1003' => array('name' => 'FortiGate 111C'),
'.1.3.6.1.4.1.12356.101.1.1004' => array('name' => 'FortiGate 100D'),
'.1.3.6.1.4.1.12356.101.1.1005' => array('name' => 'FortiRugged 100C'),
'.1.3.6.1.4.1.12356.101.1.12400' => array('name' => 'FortiGate 1240B'),
'.1.3.6.1.4.1.12356.101.1.1401' => array('name' => 'FortiGate 140D'),
'.1.3.6.1.4.1.12356.101.1.1402' => array('name' => 'FortiGate 140P'),
'.1.3.6.1.4.1.12356.101.1.1403' => array('name' => 'FortiGate 140T'),
'.1.3.6.1.4.1.12356.101.1.20' => array('name' => 'FortiGate VM'),
'.1.3.6.1.4.1.12356.101.1.2000' => array('name' => 'FortiGate 200'),
'.1.3.6.1.4.1.12356.101.1.2001' => array('name' => 'FortiGate 200A'),
'.1.3.6.1.4.1.12356.101.1.2002' => array('name' => 'FortiGate 224B'),
'.1.3.6.1.4.1.12356.101.1.2003' => array('name' => 'FortiGate 200A'),
'.1.3.6.1.4.1.12356.101.1.2004' => array('name' => 'FortiGate 200BPOE'),
'.1.3.6.1.4.1.12356.101.1.2005' => array('name' => 'FortiGate 200D'),
'.1.3.6.1.4.1.12356.101.1.2006' => array('name' => 'FortiGate 240D'),
'.1.3.6.1.4.1.12356.101.1.210' => array('name' => 'FortiWiFi 20C'),
'.1.3.6.1.4.1.12356.101.1.212' => array('name' => 'FortiGate 20C'),
'.1.3.6.1.4.1.12356.101.1.213' => array('name' => 'FortiWiFi 20CA'),
'.1.3.6.1.4.1.12356.101.1.214' => array('name' => 'FortiGate 20CA'),
'.1.3.6.1.4.1.12356.101.1.30' => array('name' => 'FortiGate VM64'),
'.1.3.6.1.4.1.12356.101.1.3000' => array('name' => 'FortiGate 300'),
'.1.3.6.1.4.1.12356.101.1.30000' => array('name' => 'FortiGate 3000'),
'.1.3.6.1.4.1.12356.101.1.3001' => array('name' => 'FortiGate 300A'),
'.1.3.6.1.4.1.12356.101.1.3002' => array('name' => 'FortiGate 310B'),
'.1.3.6.1.4.1.12356.101.1.3003' => array('name' => 'FortiGate 300D'),
'.1.3.6.1.4.1.12356.101.1.3004' => array('name' => 'FortiGate 311B'),
'.1.3.6.1.4.1.12356.101.1.3005' => array('name' => 'FortiGate 300C'),
'.1.3.6.1.4.1.12356.101.1.30160' => array('name' => 'FortiGate 3016B'),
'.1.3.6.1.4.1.12356.101.1.302' => array('name' => 'FortiGate 30B'),
'.1.3.6.1.4.1.12356.101.1.30400' => array('name' => 'FortiGate 3040B'),
'.1.3.6.1.4.1.12356.101.1.30401' => array('name' => 'FortiGate 3140B'),
'.1.3.6.1.4.1.12356.101.1.310' => array('name' => 'FortiWiFi 30B'),
'.1.3.6.1.4.1.12356.101.1.32401' => array('name' => 'FortiGate 3240C'),
'.1.3.6.1.4.1.12356.101.1.36000' => array('name' => 'FortiGate 3600'),
'.1.3.6.1.4.1.12356.101.1.36003' => array('name' => 'FortiGate 3600A'),
'.1.3.6.1.4.1.12356.101.1.36004' => array('name' => 'FortiGate 3600C'),
'.1.3.6.1.4.1.12356.101.1.38100' => array('name' => 'FortiGate 3810A'),
'.1.3.6.1.4.1.12356.101.1.39500' => array('name' => 'FortiGate 3950B'),
'.1.3.6.1.4.1.12356.101.1.39501' => array('name' => 'FortiGate 3951B'),
'.1.3.6.1.4.1.12356.101.1.40' => array('name' => 'FortiGate VM64XEN'),
'.1.3.6.1.4.1.12356.101.1.4000' => array('name' => 'FortiGate 400'),
'.1.3.6.1.4.1.12356.101.1.4001' => array('name' => 'FortiGate 400A'),
'.1.3.6.1.4.1.12356.101.1.410' => array('name' => 'FortiGate 40C'),
'.1.3.6.1.4.1.12356.101.1.411' => array('name' => 'FortiWiFi 40C'),
'.1.3.6.1.4.1.12356.101.1.500' => array('name' => 'FortiGate 50A'),
'.1.3.6.1.4.1.12356.101.1.5000' => array('name' => 'FortiGate 500'),
'.1.3.6.1.4.1.12356.101.1.50001' => array('name' => 'FortiGate 5002FB2'),
'.1.3.6.1.4.1.12356.101.1.5001' => array('name' => 'FortiGate 500A'),
'.1.3.6.1.4.1.12356.101.1.50010' => array('name' => 'FortiGate 5001'),
'.1.3.6.1.4.1.12356.101.1.50011' => array('name' => 'FortiGate 5001A'),
'.1.3.6.1.4.1.12356.101.1.50012' => array('name' => 'FortiGate 5001FA2'),
'.1.3.6.1.4.1.12356.101.1.50013' => array('name' => 'FortiGate 5001B'),
'.1.3.6.1.4.1.12356.101.1.50014' => array('name' => 'FortiGate 5001C'),
'.1.3.6.1.4.1.12356.101.1.50023' => array('name' => 'FortiSwitch 5203B'),
'.1.3.6.1.4.1.12356.101.1.50051' => array('name' => 'FortiGate 5005FA2'),
'.1.3.6.1.4.1.12356.101.1.502' => array('name' => 'FortiGate 50B'),
'.1.3.6.1.4.1.12356.101.1.504' => array('name' => 'FortiGate 51B'),
'.1.3.6.1.4.1.12356.101.1.510' => array('name' => 'FortiWiFi 50B'),
'.1.3.6.1.4.1.12356.101.1.51010' => array('name' => 'FortiGate 5101C'),
'.1.3.6.1.4.1.12356.101.1.600' => array('name' => 'FortiGate 60'),
'.1.3.6.1.4.1.12356.101.1.6003' => array('name' => 'FortiGate 600C'),
'.1.3.6.1.4.1.12356.101.1.601' => array('name' => 'FortiGate 60M'),
'.1.3.6.1.4.1.12356.101.1.602' => array('name' => 'FortiGate 60ADSL'),
'.1.3.6.1.4.1.12356.101.1.603' => array('name' => 'FortiGate 60B'),
'.1.3.6.1.4.1.12356.101.1.610' => array('name' => 'FortiWiFi 60'),
'.1.3.6.1.4.1.12356.101.1.611' => array('name' => 'FortiWiFi 60A'),
'.1.3.6.1.4.1.12356.101.1.612' => array('name' => 'FortiWiFi 60AM'),
'.1.3.6.1.4.1.12356.101.1.613' => array('name' => 'FortiWiFi 60B'),
'.1.3.6.1.4.1.12356.101.1.615' => array('name' => 'FortiGate 60C'),
'.1.3.6.1.4.1.12356.101.1.616' => array('name' => 'FortiWiFi 30D'),
'.1.3.6.1.4.1.12356.101.1.616' => array('name' => 'FortiWiFi 60C'),
'.1.3.6.1.4.1.12356.101.1.616' => array('name' => 'FortiWiFi 30DPOE'),
'.1.3.6.1.4.1.12356.101.1.617' => array('name' => 'FortiWiFi 60CM'),
'.1.3.6.1.4.1.12356.101.1.618' => array('name' => 'FortiWiFi 60CA'),
'.1.3.6.1.4.1.12356.101.1.619' => array('name' => 'FortiWiFi 6XMB'),
'.1.3.6.1.4.1.12356.101.1.6200' => array('name' => 'FortiGate 620B'),
'.1.3.6.1.4.1.12356.101.1.6201' => array('name' => 'FortiGate 600D'),
'.1.3.6.1.4.1.12356.101.1.621' => array('name' => 'FortiGate 60CP'),
'.1.3.6.1.4.1.12356.101.1.6210' => array('name' => 'FortiGate 621B'),
'.1.3.6.1.4.1.12356.101.1.625' => array('name' => 'FortiGate 30D'),
'.1.3.6.1.4.1.12356.101.1.625' => array('name' => 'FortiGate 60D'),
'.1.3.6.1.4.1.12356.101.1.625' => array('name' => 'FortiGate 30DPOE'),
'.1.3.6.1.4.1.12356.101.1.626' => array('name' => 'FortiWiFi 60D'),
'.1.3.6.1.4.1.12356.101.1.630' => array('name' => 'FortiGate 90D'),
'.1.3.6.1.4.1.12356.101.1.631' => array('name' => 'FortiGate 90DPOE'),
'.1.3.6.1.4.1.12356.101.1.632' => array('name' => 'FortiWiFi 90D'),
'.1.3.6.1.4.1.12356.101.1.633' => array('name' => 'FortiWiFi 90DPOE'),
'.1.3.6.1.4.1.12356.101.1.800' => array('name' => 'FortiGate 80C'),
'.1.3.6.1.4.1.12356.101.1.8000' => array('name' => 'FortiGate 800'),
'.1.3.6.1.4.1.12356.101.1.8001' => array('name' => 'FortiGate 800F'),
'.1.3.6.1.4.1.12356.101.1.8003' => array('name' => 'FortiGate 800C'),
'.1.3.6.1.4.1.12356.101.1.801' => array('name' => 'FortiGate 80CM'),
'.1.3.6.1.4.1.12356.101.1.802' => array('name' => 'FortiGate 82C'),
'.1.3.6.1.4.1.12356.101.1.810' => array('name' => 'FortiWiFi 80CM'),
'.1.3.6.1.4.1.12356.101.1.811' => array('name' => 'FortiWiFi 81CM'),
);

$rewrite_breeze_type = array(
  'aubs'     => 'AU-BS',    // modular access unit
  'ausa'     => 'AU-SA',    // stand-alone access unit
  'su-6-1d'  => 'SU-6-1D',  // subscriber unit supporting 6 Mbps (after 5.0 - deprecated)
  'su-6-bd'  => 'SU-6-BD',  // subscriber unit supporting 6 Mbps
  'su-24-bd' => 'SU-24-BD', // subscriber unit supporting 24 Mbps
  'bu-b14'   => 'BU-B14',   // BreezeNET Base Unit supporting 14 Mbps
  'bu-b28'   => 'BU-B28',   // BreezeNET Base Unit supporting 28 Mbps
  'rb-b14'   => 'RB-B14',   // BreezeNET Remote Bridge supporting 14 Mbps
  'rb-b28'   => 'RB-B28',   // BreezeNET Remote Bridge supporting 28 Mbps
  'su-bd'    => 'SU-BD',    // subscriber unit
  'su-54-bd' => 'SU-54-BD', // subscriber unit supporting 54 Mbps
  'su-3-1d'  => 'SU-3-1D',  // subscriber unit supporting 3 Mbps (after 5.0 - deprecated)
  'su-3-4d'  => 'SU-3-4D',  // subscriber unit supporting 3 Mbps
  'ausbs'    => 'AUS-BS',   // modular access unit supporting maximum 25 subscribers
  'aussa'    => 'AUS-SA',   // stand-alone access unit supporting maximum 25 subscribers
  'aubs4900' => 'AU-BS-4900', // BreezeAccess 4900 modular access unit
  'ausa4900' => 'AU-SA-4900', // BreezeAccess 4900 stand alone access unit
  'subd4900' => 'SU-BD-4900', // BreezeAccess 4900 subscriber unit
  'bu-b100'  => 'BU-B100',  // BreezeNET Base Unit unlimited throughput
  'rb-b100'  => 'BU-B100',  // BreezeNET Remote Bridge unlimited throughput
  'su-i'     => 'SU-I',
  'au-ez'    => 'AU-EZ',
  'su-ez'    => 'SU-EZ',
  'su-v'     => 'SU-V',     // subscriber unit supporting 12 Mbps downlink and 8 Mbps uplink
  'bu-b10'   => 'BU-B10',   // BreezeNET Base Unit supporting 5 Mbps
  'rb-b10'   => 'RB-B10',   // BreezeNET Base Unit supporting 5 Mbps
  'su-8-bd'  => 'SU-8-BD',  // subscriber unit supporting 8 Mbps
  'su-1-bd'  => 'SU-1-BD',  // subscriber unit supporting 1 Mbps
  'su-3-l'   => 'SU-3-L',   // subscriber unit supporting 3 Mbps
  'su-6-l'   => 'SU-6-L',   // subscriber unit supporting 6 Mbps
  'su-12-l'  => 'SU-12-L',  // subscriber unit supporting 12 Mbps
  'au'       => 'AU',       // security access unit
  'su'       => 'SU',       // security subscriber unit
);

$rewrite_extreme_hardware = array (
  'ags100-24t' => 'AGS 100-24t',
  'ags150-24p' => 'AGS 150-24p',
  'alpine3802' => 'Alpine 3802',
  'alpine3804' => 'Alpine 3804',
  'alpine3808' => 'Alpine 3808',
  'altitude300' => 'Altitude 300',
  'altitude350' => 'Altitude 350',
  'altitude3510' => 'Altitude 3510',
  'altitude3550' => 'Altitude 3550',
  'altitude360' => 'Altitude 360',
  'altitude450' => 'Altitude 450',
  'altitude4610' => 'Altitude 4610',
  'altitude4620' => 'Altitude 4620',
  'altitude4700' => 'Altitude 4700',
  'bd10808' => 'Black Diamond 10808',
  'bd12802' => 'Black Diamond 12802',
  'bd12804' => 'Black Diamond 12804',
  'bd20804' => 'Black Diamond 20804',
  'bd20808' => 'Black Diamond 20808',
  'bd8806' => 'Black Diamond 8806',
  'bd8810' => 'Black Diamond 8810',
  'bdx8' => 'Black Diamond X8',
  'blackDiamond6800' => 'Black Diamond 6800',
  'blackDiamond6804' => 'Black Diamond 6804',
  'blackDiamond6808' => 'Black Diamond 6808',
  'blackDiamond6816' => 'Black Diamond 6816',
  'e4g-200' => 'E4G-200',
  'e4g-400' => 'E4G-400',
  'enetSwitch24Port' => 'EnetSwitch 24Port',
  'nwi-e450a' => 'NWI-e450a',
  'sentriantAG200' => 'Sentriant AG200',
  'sentriantAGSW' => 'Sentriant AGSW',
  'sentriantCE150' => 'Sentriant CE150',
  'sentriantNG300' => 'Sentriant NG300',
  'sentriantPS200v1' => 'Sentriant PS200v1',
  'summit1' => 'Summit 1',
  'summit1iSX' => 'Summit 1iSX',
  'summit1iTX' => 'Summit 1iTX',
  'summit2' => 'Summit 2',
  'summit200-24' => 'Summit 200-24',
  'summit200-24fx' => 'Summit 200-24fx',
  'summit200-48' => 'Summit 200-48',
  'summit24' => 'Summit 24',
  'summit24e2SX' => 'Summit 24e2SX',
  'summit24e2TX' => 'Summit 24e2TX',
  'summit24e3' => 'Summit 24e3',
  'summit3' => 'Summit 3',
  'summit300-24' => 'Summit 300-24',
  'summit300-48' => 'Summit 300-48',
  'summit4' => 'Summit 4',
  'summit400-24p' => 'Summit 400-24p',
  'summit400-24t' => 'Summit 400-24t',
  'summit400-48t' => 'Summit 400-48t',
  'summit48' => 'Summit 48',
  'summit48i' => 'Summit 48i',
  'summit48si' => 'Summit 48si',
  'summit4fx' => 'Summit 4fx',
  'summit5i' => 'Summit 5i',
  'summit5iLX' => 'Summit 5iLX',
  'summit5iTX' => 'Summit 5iTX',
  'summit7iSX' => 'Summit 7iSX',
  'summit7iTX' => 'Summit 7iTX',
  'summitPx1' => 'Summit Px1',
  'summitStack' => 'Summit Stack',
  'summitVer2Stack' => 'Summit Stack V2',
  'summitWM100' => 'Summit WM100',
  'summitWM1000' => 'Summit WM1000',
  'summitWM100Lite' => 'Summit WM100Lite',
  'summitWM20' => 'Summit WM20',
  'summitWM200' => 'Summit WM200',
  'summitWM2000' => 'Summit WM2000',
  'summitWM3400' => 'Summit WM3400',
  'summitWM3600' => 'Summit WM3600',
  'summitWM3700' => 'Summit WM3700',
  'summitX150-24p' => 'Summit X150-24p',
  'summitX150-24t' => 'Summit X150-24t',
  'summitX150-24tDC' => 'Summit X150-24tDC',
  'summitX150-24x' => 'Summit X150-24x',
  'summitX150-24xDC' => 'Summit X150-24xDC',
  'summitX150-48p' => 'Summit X150-48p',
  'summitX150-48t' => 'Summit X150-48t',
  'summitX150-48tDC' => 'Summit X150-48tDC',
  'summitX250-24p' => 'Summit X250-24p',
  'summitX250-24t' => 'Summit X250-24t',
  'summitX250-24tDC' => 'Summit X250-24tDC',
  'summitX250-24x' => 'Summit X250-24x',
  'summitX250-24xDC' => 'Summit X250-24xDC',
  'summitX250-48p' => 'Summit X250-48p',
  'summitX250-48t' => 'Summit X250-48t',
  'summitX250-48tDC' => 'Summit X250-48tDC',
  'summitX350-24t' => 'Summit X350-24t',
  'summitX350-48t' => 'Summit X350-48t',
  'summitX440-24p' => 'Summit X440-24p',
  'summitX440-24p-10G' => 'Summit X440-24p-10G',
  'summitX440-24t' => 'Summit X440-24t',
  'summitX440-24t-10G' => 'Summit X440-24t-10G',
  'summitX440-24tdc' => 'Summit X440-24tdc',
  'summitX440-24x' => 'Summit X440-24x',
  'summitX440-24x-10g' => 'Summit X440-24x-10g',
  'summitX440-48p' => 'Summit X440-48p',
  'summitX440-48p-10G' => 'Summit X440-48p-10G',
  'summitX440-48t' => 'Summit X440-48t',
  'summitX440-48t-10G' => 'Summit X440-48t-10G',
  'summitX440-48tdc' => 'Summit X440-48tdc',
  'summitX440-8p' => 'Summit X440-8p',
  'summitX440-8t' => 'Summit X440-8t',
  'summitX440-L2-24t' => 'Summit X440-L2-24t',
  'summitX440-L2-48t' => 'Summit X440-L2-48t',
  'summitX450-24t' => 'Summit X450-24t',
  'summitX450-24x' => 'Summit X450-24x',
  'summitX450a-24t ' => 'Summit X450a-24t ',
  'summitX450a-24tDC' => 'Summit X450a-24tDC',
  'summitX450a-24x' => 'Summit X450a-24x',
  'summitX450a-24xDC' => 'Summit X450a-24xDC',
  'summitX450a-48t' => 'Summit X450a-48t',
  'summitX450a-48tDC' => 'Summit X450a-48tDC',
  'summitX450e-24p ' => 'Summit X450e-24p ',
  'summitX450e-24t' => 'Summit X450e-24t',
  'summitX450e-48p' => 'Summit X450e-48p',
  'summitX450e-48t' => 'Summit X450e-48t',
  'summitX460-24p' => 'Summit X460-24p',
  'summitX460-24t' => 'Summit X460-24t',
  'summitX460-24x' => 'Summit X460-24x',
  'summitX460-48p' => 'Summit X460-48p',
  'summitX460-48t' => 'Summit X460-48t',
  'summitX460-48x' => 'Summit X460-48x',
  'summitX480-24x' => 'Summit X480-24x',
  'summitX480-24x-10G4X' => 'Summit X480-24x-10G4X',
  'summitX480-24x-40G4X' => 'Summit X480-24x-40G4X',
  'summitX480-24x-SS' => 'Summit X480-24x-SS',
  'summitX480-24x-SS128' => 'Summit X480-24x-SS128',
  'summitX480-24x-SSV80' => 'Summit X480-24x-SSV80',
  'summitX480-48t' => 'Summit X480-48t',
  'summitX480-48t-10G4X' => 'Summit X480-48t-10G4X',
  'summitX480-48t-40G4X' => 'Summit X480-48t-40G4X',
  'summitX480-48t-SS' => 'Summit X480-48t-SS',
  'summitX480-48t-SS128' => 'Summit X480-48t-SS128',
  'summitX480-48t-SSV80' => 'Summit X480-48t-SSV80',
  'summitX480-48x' => 'Summit X480-48x',
  'summitX480-48x-10G4X' => 'Summit X480-48x-10G4X',
  'summitX480-48x-40G4X' => 'Summit X480-48x-40G4X',
  'summitX480-48x-SS' => 'Summit X480-48x-SS',
  'summitX480-48x-SS128' => 'Summit X480-48x-SS128',
  'summitX480-48x-SSV80' => 'Summit X480-48x-SSV80',
  'summitX650-24t' => 'Summit X650-24t',
  'summitX650-24t-10G8X' => 'Summit X650-24t-10G8X',
  'summitX650-24t-40G4X' => 'Summit X650-24t-40G4X',
  'summitX650-24t-SS' => 'Summit X650-24t-SS',
  'summitX650-24t-SS256' => 'Summit X650-24t-SS256',
  'summitX650-24t-SS512' => 'Summit X650-24t-SS512',
  'summitX650-24t-SSns' => 'Summit X650-24t-SSns',
  'summitX650-24x' => 'Summit X650-24x',
  'summitX650-24x-10G8X' => 'Summit X650-24x-10G8X',
  'summitX650-24x-40G4X' => 'Summit X650-24x-40G4X',
  'summitX650-24x-SS' => 'Summit X650-24x-SS',
  'summitX650-24x-SS256' => 'Summit X650-24x-SS256',
  'summitX650-24x-SS512' => 'Summit X650-24x-SS512',
  'summitX650-24x-SSns' => 'Summit X650-24x-SSns',
  'summitX670-48x' => 'Summit X670-48x',
  'summitX670v-48t' => 'Summit X670v-48t',
  'summitX670v-48x' => 'Summit X670v-48x',
);

$rewrite_cpqida_hardware = array(
  'other' => 'Other',
  'ida' => 'IDA',
  'idaExpansion' => 'IDA Expansion',
  'ida-2' => 'IDA - 2',
  'smart' => 'SMART',
  'smart-2e' => 'SMART - 2/E',
  'smart-2p' => 'SMART - 2/P',
  'smart-2sl' => 'SMART - 2SL',
  'smart-3100es' => 'Smart - 3100ES',
  'smart-3200' => 'Smart - 3200',
  'smart-2dh' => 'SMART - 2DH',
  'smart-221' => 'Smart - 221',
  'sa-4250es' => 'Smart Array 4250ES',
  'sa-4200' => 'Smart Array 4200',
  'sa-integrated' => 'Integrated Smart Array',
  'sa-431' => 'Smart Array 431',
  'sa-5300' => 'Smart Array 5300',
  'raidLc2' => 'RAID LC2 Controller',
  'sa-5i' => 'Smart Array 5i',
  'sa-532' => 'Smart Array 532',
  'sa-5312' => 'Smart Array 5312',
  'sa-641' => 'Smart Array 641',
  'sa-642' => 'Smart Array 642',
  'sa-6400' => 'Smart Array 6400',
  'sa-6400em' => 'Smart Array 6400 EM',
  'sa-6i' => 'Smart Array 6i',
  'sa-generic' => 'Generic Array',
  'sa-p600' => 'Smart Array P600',
  'sa-p400' => 'Smart Array P400',
  'sa-e200' => 'Smart Array E200',
  'sa-e200i' => 'Smart Array E200i',
  'sa-p400i' => 'Smart Array P400i',
  'sa-p800' => 'Smart Array P800',
  'sa-e500' => 'Smart Array E500',
  'sa-p700m' => 'Smart Array P700m',
  'sa-p212' => 'Smart Array P212',
  'sa-p410' => 'Smart Array P410',
  'sa-p410i' => 'Smart Array P410i',
  'sa-p411' => 'Smart Array P411',
  'sa-b110i' => 'Smart Array B110i SATA RAID',
  'sa-p712m' => 'Smart Array P712m',
  'sa-p711m' => 'Smart Array P711m',
  'sa-p812' => 'Smart Array P812',
  'sw-1210m' => 'StorageWorks 1210m',
  'sa-p220i' => 'Smart Array P220i',
  'sa-p222' => 'Smart Array P222',
  'sa-p420' => 'Smart Array P420',
  'sa-p420i' => 'Smart Array P420i',
  'sa-p421' => 'Smart Array P421',
  'sa-b320i' => 'Smart Array B320i',
  'sa-p822' => 'Smart Array P822',
  'sa-p721m' => 'Smart Array P721m',
  'sa-b120i' => 'Smart Array B120i',
  'hps-1224' => 'HP Storage p1224',
  'hps-1228' => 'HP Storage p1228',
  'hps-1228m' => 'HP Storage p1228m',
  'sa-p822se' => 'Smart Array P822se',
  'hps-1224e' => 'HP Storage p1224e',
  'hps-1228e' => 'HP Storage p1228e',
  'hps-1228em' => 'HP Storage p1228em',
  'sa-p230i' => 'Smart Array P230i',
  'sa-p430i' => 'Smart Array P430i',
  'sa-p430' => 'Smart Array P430',
  'sa-p431' => 'Smart Array P431',
  'sa-p731m' => 'Smart Array P731m',
  'sa-p830i' => 'Smart Array P830i',
  'sa-p830' => 'Smart Array P830',
  'sa-p831' => 'Smart Array P831'
);

$rewrite_aosw_hardware = array(

  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.1'  => 'OmniAccess 5000',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.2'  => 'OmniAccess 4024',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.3'  => 'OmniAccess 4308',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.4'  => 'OmniAccess 6000',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.5'  => 'OmniAccess 4302',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.6'  => 'OmniAccess 4504',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.7'  => 'OmniAccess 4604',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.8'  => 'OmniAccess 4704',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.9'  => 'OmniAccess 4304',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.10' => 'OmniAccess 4306',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.11' => 'OmniAccess 4306G',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.1.12' => 'OmniAccess 4306GW',

  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.1'  => 'OmniAccess AP60',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.2'  => 'OmniAccess AP61',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.3'  => 'OmniAccess AP70',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.4'  => 'OmniAccess AP80S',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.5'  => 'OmniAccess AP80M',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.6'  => 'OmniAccess AP65',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.7'  => 'OmniAccess AP40',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.8'  => 'OmniAccess AP85',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.9'  => 'OmniAccess AP41',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.10' => 'OmniAccess AP120',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.11' => 'OmniAccess AP121',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.12' => 'OmniAccess AP124',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.13' => 'OmniAccess AP125',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.14' => 'OmniAccess AP120ABG',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.15' => 'OmniAccess AP121ABG',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.16' => 'OmniAccess AP124ABG',
  '1.3.6.1.4.1.6486.800.1.1.2.2.2.1.2.17' => 'OmniAccess AP125ABG',
);

$rewrite_ironware_hardware = array(
'.1.3.6.1.4.1.1991.1.3.1' => array('name' => 'snFastIron', 'object' => 'snFastIron'),
'.1.3.6.1.4.1.1991.1.3.1.1' => array('name' => 'Stackable FastIron workgroup', 'object' => 'snFIWGSwitch'),
'.1.3.6.1.4.1.1991.1.3.1.2' => array('name' => 'Stackable FastIron backbone', 'object' => 'snFIBBSwitch'),
'.1.3.6.1.4.1.1991.1.3.10' => array('name' => 'snNetIron400', 'object' => 'snNetIron400'),
'.1.3.6.1.4.1.1991.1.3.10.1' => array('name' => 'NetIron 400', 'object' => 'snNI400Router'),
'.1.3.6.1.4.1.1991.1.3.11' => array('name' => 'snNetIron800', 'object' => 'snNetIron800'),
'.1.3.6.1.4.1.1991.1.3.11.1' => array('name' => 'NetIron 800', 'object' => 'snNI800Router'),
'.1.3.6.1.4.1.1991.1.3.12' => array('name' => 'snFastIron2GC', 'object' => 'snFastIron2GC'),
'.1.3.6.1.4.1.1991.1.3.12.1' => array('name' => 'FastIron II GC', 'object' => 'snFI2GCSwitch'),
'.1.3.6.1.4.1.1991.1.3.12.2' => array('name' => 'FastIron II GC', 'object' => 'snFI2GCRouter'),
'.1.3.6.1.4.1.1991.1.3.13' => array('name' => 'snFastIron2PlusGC', 'object' => 'snFastIron2PlusGC'),
'.1.3.6.1.4.1.1991.1.3.13.1' => array('name' => 'FastIron II Plus GC', 'object' => 'snFI2PlusGCSwitch'),
'.1.3.6.1.4.1.1991.1.3.13.2' => array('name' => 'FastIron II Plus GC', 'object' => 'snFI2PlusGCRouter'),
'.1.3.6.1.4.1.1991.1.3.14' => array('name' => 'snBigIron15000', 'object' => 'snBigIron15000'),
'.1.3.6.1.4.1.1991.1.3.14.1' => array('name' => 'BigIron 15000', 'object' => 'snBI15000Switch'),
'.1.3.6.1.4.1.1991.1.3.14.2' => array('name' => 'BigIron 15000', 'object' => 'snBI15000Router'),
'.1.3.6.1.4.1.1991.1.3.14.3' => array('name' => 'snBI15000SI', 'object' => 'snBI15000SI'),
'.1.3.6.1.4.1.1991.1.3.15' => array('name' => 'snNetIron1500', 'object' => 'snNetIron1500'),
'.1.3.6.1.4.1.1991.1.3.15.1' => array('name' => 'NetIron 1500', 'object' => 'snNI1500Router'),
'.1.3.6.1.4.1.1991.1.3.16' => array('name' => 'snFastIron3', 'object' => 'snFastIron3'),
'.1.3.6.1.4.1.1991.1.3.16.1' => array('name' => 'FastIron III', 'object' => 'snFI3Switch'),
'.1.3.6.1.4.1.1991.1.3.16.2' => array('name' => 'FastIron III', 'object' => 'snFI3Router'),
'.1.3.6.1.4.1.1991.1.3.17' => array('name' => 'snFastIron3GC', 'object' => 'snFastIron3GC'),
'.1.3.6.1.4.1.1991.1.3.17.1' => array('name' => 'FastIron III GC', 'object' => 'snFI3GCSwitch'),
'.1.3.6.1.4.1.1991.1.3.17.2' => array('name' => 'FastIron III GC', 'object' => 'snFI3GCRouter'),
'.1.3.6.1.4.1.1991.1.3.18' => array('name' => 'snServerIron400', 'object' => 'snServerIron400'),
'.1.3.6.1.4.1.1991.1.3.18.1' => array('name' => 'ServerIron 400', 'object' => 'snSI400Switch'),
'.1.3.6.1.4.1.1991.1.3.18.2' => array('name' => 'ServerIron 400', 'object' => 'snSI400Router'),
'.1.3.6.1.4.1.1991.1.3.19' => array('name' => 'snServerIron800', 'object' => 'snServerIron800'),
'.1.3.6.1.4.1.1991.1.3.19.1' => array('name' => 'ServerIron800', 'object' => 'snSI800Switch'),
'.1.3.6.1.4.1.1991.1.3.19.2' => array('name' => 'ServerIron800', 'object' => 'snSI800Router'),
'.1.3.6.1.4.1.1991.1.3.2' => array('name' => 'snNetIron', 'object' => 'snNetIron'),
'.1.3.6.1.4.1.1991.1.3.2.1' => array('name' => 'Stackable NetIron', 'object' => 'snNIRouter'),
'.1.3.6.1.4.1.1991.1.3.20' => array('name' => 'snServerIron1500', 'object' => 'snServerIron1500'),
'.1.3.6.1.4.1.1991.1.3.20.1' => array('name' => 'ServerIron1500', 'object' => 'snSI1500Switch'),
'.1.3.6.1.4.1.1991.1.3.20.2' => array('name' => 'ServerIron1500', 'object' => 'snSI1500Router'),
'.1.3.6.1.4.1.1991.1.3.21' => array('name' => 'sn4802', 'object' => 'sn4802'),
'.1.3.6.1.4.1.1991.1.3.21.1' => array('name' => 'Stackable 4802', 'object' => 'sn4802Switch'),
'.1.3.6.1.4.1.1991.1.3.21.2' => array('name' => 'Stackable 4802', 'object' => 'sn4802Router'),
'.1.3.6.1.4.1.1991.1.3.21.3' => array('name' => 'Stackable 4802 ServerIron', 'object' => 'sn4802SI'),
'.1.3.6.1.4.1.1991.1.3.22' => array('name' => 'snFastIron400', 'object' => 'snFastIron400'),
'.1.3.6.1.4.1.1991.1.3.22.1' => array('name' => 'FastIron 400', 'object' => 'snFI400Switch'),
'.1.3.6.1.4.1.1991.1.3.22.2' => array('name' => 'FastIron 400', 'object' => 'snFI400Router'),
'.1.3.6.1.4.1.1991.1.3.23' => array('name' => 'snFastIron800', 'object' => 'snFastIron800'),
'.1.3.6.1.4.1.1991.1.3.23.1' => array('name' => 'FastIron800', 'object' => 'snFI800Switch'),
'.1.3.6.1.4.1.1991.1.3.23.2' => array('name' => 'FastIron800', 'object' => 'snFI800Router'),
'.1.3.6.1.4.1.1991.1.3.24' => array('name' => 'snFastIron1500', 'object' => 'snFastIron1500'),
'.1.3.6.1.4.1.1991.1.3.24.1' => array('name' => 'FastIron1500', 'object' => 'snFI1500Switch'),
'.1.3.6.1.4.1.1991.1.3.24.2' => array('name' => 'FastIron1500', 'object' => 'snFI1500Router'),
'.1.3.6.1.4.1.1991.1.3.25' => array('name' => 'FES 2402', 'object' => 'snFES2402'),
'.1.3.6.1.4.1.1991.1.3.25.1' => array('name' => 'FES2402', 'object' => 'snFES2402Switch'),
'.1.3.6.1.4.1.1991.1.3.25.2' => array('name' => 'FES2402', 'object' => 'snFES2402Router'),
'.1.3.6.1.4.1.1991.1.3.26' => array('name' => 'FES 4802', 'object' => 'snFES4802'),
'.1.3.6.1.4.1.1991.1.3.26.1' => array('name' => 'FES4802', 'object' => 'snFES4802Switch'),
'.1.3.6.1.4.1.1991.1.3.26.2' => array('name' => 'FES4802', 'object' => 'snFES4802Router'),
'.1.3.6.1.4.1.1991.1.3.27' => array('name' => 'FES 9604', 'object' => 'snFES9604'),
'.1.3.6.1.4.1.1991.1.3.27.1' => array('name' => 'FES9604', 'object' => 'snFES9604Switch'),
'.1.3.6.1.4.1.1991.1.3.27.2' => array('name' => 'FES9604', 'object' => 'snFES9604Router'),
'.1.3.6.1.4.1.1991.1.3.28' => array('name' => 'FES 12GCF ', 'object' => 'snFES12GCF'),
'.1.3.6.1.4.1.1991.1.3.28.1' => array('name' => 'FES12GCF ', 'object' => 'snFES12GCFSwitch'),
'.1.3.6.1.4.1.1991.1.3.28.2' => array('name' => 'FES12GCF', 'object' => 'snFES12GCFRouter'),
'.1.3.6.1.4.1.1991.1.3.29' => array('name' => 'snFES2402POE', 'object' => 'snFES2402POE'),
'.1.3.6.1.4.1.1991.1.3.29.1' => array('name' => 'snFES2402POESwitch', 'object' => 'snFES2402POESwitch'),
'.1.3.6.1.4.1.1991.1.3.29.2' => array('name' => 'snFES2402POERouter', 'object' => 'snFES2402POERouter'),
'.1.3.6.1.4.1.1991.1.3.3' => array('name' => 'snServerIron', 'object' => 'snServerIron'),
'.1.3.6.1.4.1.1991.1.3.3.1' => array('name' => 'Stackable ServerIron', 'object' => 'snSI'),
'.1.3.6.1.4.1.1991.1.3.3.2' => array('name' => 'Stackable ServerIronXL', 'object' => 'snSIXL'),
'.1.3.6.1.4.1.1991.1.3.3.3' => array('name' => 'Stackable ServerIronXL TCS', 'object' => 'snSIXLTCS'),
'.1.3.6.1.4.1.1991.1.3.30' => array('name' => 'snFES4802POE', 'object' => 'snFES4802POE'),
'.1.3.6.1.4.1.1991.1.3.30.1' => array('name' => 'snFES4802POESwitch', 'object' => 'snFES4802POESwitch'),
'.1.3.6.1.4.1.1991.1.3.30.2' => array('name' => 'snFES4802POERouter', 'object' => 'snFES4802POERouter'),
'.1.3.6.1.4.1.1991.1.3.31' => array('name' => 'snNetIron4802', 'object' => 'snNetIron4802'),
'.1.3.6.1.4.1.1991.1.3.31.1' => array('name' => 'NetIron 4802', 'object' => 'snNI4802Switch'),
'.1.3.6.1.4.1.1991.1.3.31.2' => array('name' => 'NetIron 4802', 'object' => 'snNI4802Router'),
'.1.3.6.1.4.1.1991.1.3.32' => array('name' => 'snBigIronMG8', 'object' => 'snBigIronMG8'),
'.1.3.6.1.4.1.1991.1.3.32.1' => array('name' => 'BigIron MG8', 'object' => 'snBIMG8Switch'),
'.1.3.6.1.4.1.1991.1.3.32.2' => array('name' => 'BigIron MG8', 'object' => 'snBIMG8Router'),
'.1.3.6.1.4.1.1991.1.3.33' => array('name' => 'snNetIron40G', 'object' => 'snNetIron40G'),
'.1.3.6.1.4.1.1991.1.3.33.2' => array('name' => 'NetIron 40G', 'object' => 'snNI40GRouter'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.1' => array('name' => 'FES 24G', 'object' => 'snFESX424'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.1.1' => array('name' => 'FESX424', 'object' => 'snFESX424Switch'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.1.2' => array('name' => 'FESX424', 'object' => 'snFESX424Router'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.2' => array('name' => 'FES 24G-PREM', 'object' => 'snFESX424Prem'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.2.1' => array('name' => 'FESX424-PREM', 'object' => 'snFESX424PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.1.1.2.2' => array('name' => 'FESX424-PREM', 'object' => 'snFESX424PremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.1' => array('name' => 'FES 24G + 1 10G', 'object' => 'snFESX424Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.1.1' => array('name' => 'FESX424+1XG', 'object' => 'snFESX424Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.1.2' => array('name' => 'FESX424+1XG', 'object' => 'snFESX424Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.2' => array('name' => 'FES 24G + 1 10G-PREM', 'object' => 'snFESX424Plus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.2.1' => array('name' => 'FESX424+1XG-PREM', 'object' => 'snFESX424Plus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.1.2.2.2' => array('name' => 'FESX424+1XG-PREM', 'object' => 'snFESX424Plus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.1' => array('name' => 'FES 24G + 2 10G', 'object' => 'snFESX424Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.1.1' => array('name' => 'FESX424+2XG', 'object' => 'snFESX424Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.1.2' => array('name' => 'FESX424+2XG', 'object' => 'snFESX424Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.2' => array('name' => 'FES 24G + 2 10G-PREM', 'object' => 'snFESX424Plus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.2.1' => array('name' => 'FESX424+2XG-PREM', 'object' => 'snFESX424Plus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.1.3.2.2' => array('name' => 'FESX424+2XG-PREM', 'object' => 'snFESX424Plus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.1' => array('name' => 'snFESX624POE', 'object' => 'snFESX624POE'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.1.1' => array('name' => 'snFESX624POESwitch', 'object' => 'snFESX624POESwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.1.2' => array('name' => 'snFESX624POERouter', 'object' => 'snFESX624POERouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.2' => array('name' => 'snFESX624POEPrem', 'object' => 'snFESX624POEPrem'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.2.1' => array('name' => 'snFESX624POEPremSwitch', 'object' => 'snFESX624POEPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.2.2' => array('name' => 'snFESX624POEPremRouter', 'object' => 'snFESX624POEPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.1.2.3' => array('name' => 'snFESX624POEPrem6Router', 'object' => 'snFESX624POEPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.1' => array('name' => 'snFESX624POEPlus1XG', 'object' => 'snFESX624POEPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.1.1' => array('name' => 'snFESX624POEPlus1XGSwitch', 'object' => 'snFESX624POEPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.1.2' => array('name' => 'snFESX624POEPlus1XGRouter', 'object' => 'snFESX624POEPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.2' => array('name' => 'snFESX624POEPlus1XGPrem', 'object' => 'snFESX624POEPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.2.1' => array('name' => 'snFESX624POEPlus1XGPremSwitch', 'object' => 'snFESX624POEPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.2.2' => array('name' => 'snFESX624POEPlus1XGPremRouter', 'object' => 'snFESX624POEPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.2.2.3' => array('name' => 'snFESX624POEPlus1XGPrem6Router', 'object' => 'snFESX624POEPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.1' => array('name' => 'snFESX624POEPlus2XG', 'object' => 'snFESX624POEPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.1.1' => array('name' => 'snFESX624POEPlus2XGSwitch', 'object' => 'snFESX624POEPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.1.2' => array('name' => 'snFESX624POEPlus2XGRouter', 'object' => 'snFESX624POEPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.2' => array('name' => 'snFESX624POEPlus2XGPrem', 'object' => 'snFESX624POEPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.2.1' => array('name' => 'snFESX624POEPlus2XGPremSwitch', 'object' => 'snFESX624POEPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.2.2' => array('name' => 'snFESX624POEPlus2XGPremRouter', 'object' => 'snFESX624POEPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.10.3.2.3' => array('name' => 'snFESX624POEPlus2XGPrem6Router', 'object' => 'snFESX624POEPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.1' => array('name' => 'snFESX624E', 'object' => 'snFESX624E'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.1.1' => array('name' => 'snFESX624ESwitch', 'object' => 'snFESX624ESwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.1.2' => array('name' => 'snFESX624ERouter', 'object' => 'snFESX624ERouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.2' => array('name' => 'snFESX624EPrem', 'object' => 'snFESX624EPrem'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.2.1' => array('name' => 'snFESX624EPremSwitch', 'object' => 'snFESX624EPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.2.2' => array('name' => 'snFESX624EPremRouter', 'object' => 'snFESX624EPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.1.2.3' => array('name' => 'snFESX624EPrem6Router', 'object' => 'snFESX624EPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.1' => array('name' => 'snFESX624EPlus1XG', 'object' => 'snFESX624EPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.1.1' => array('name' => 'snFESX624EPlus1XGSwitch', 'object' => 'snFESX624EPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.1.2' => array('name' => 'snFESX624EPlus1XGRouter', 'object' => 'snFESX624EPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.2' => array('name' => 'snFESX624EPlus1XGPrem', 'object' => 'snFESX624EPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.2.1' => array('name' => 'snFESX624EPlus1XGPremSwitch', 'object' => 'snFESX624EPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.2.2' => array('name' => 'snFESX624EPlus1XGPremRouter', 'object' => 'snFESX624EPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.2.2.3' => array('name' => 'snFESX624EPlus1XGPrem6Router', 'object' => 'snFESX624EPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.1' => array('name' => 'snFESX624EPlus2XG', 'object' => 'snFESX624EPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.1.1' => array('name' => 'snFESX624EPlus2XGSwitch', 'object' => 'snFESX624EPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.1.2' => array('name' => 'snFESX624EPlus2XGRouter', 'object' => 'snFESX624EPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.2' => array('name' => 'snFESX624EPlus2XGPrem', 'object' => 'snFESX624EPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.2.1' => array('name' => 'snFESX624EPlus2XGPremSwitch', 'object' => 'snFESX624EPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.2.2' => array('name' => 'snFESX624EPlus2XGPremRouter', 'object' => 'snFESX624EPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.11.3.2.3' => array('name' => 'snFESX624EPlus2XGPrem6Router', 'object' => 'snFESX624EPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.1' => array('name' => 'snFESX624EFiber', 'object' => 'snFESX624EFiber'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.1.1' => array('name' => 'snFESX624EFiberSwitch', 'object' => 'snFESX624EFiberSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.1.2' => array('name' => 'snFESX624EFiberRouter', 'object' => 'snFESX624EFiberRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.2' => array('name' => 'snFESX624EFiberPrem', 'object' => 'snFESX624EFiberPrem'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.2.1' => array('name' => 'snFESX624EFiberPremSwitch', 'object' => 'snFESX624EFiberPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.2.2' => array('name' => 'snFESX624EFiberPremRouter', 'object' => 'snFESX624EFiberPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.1.2.3' => array('name' => 'snFESX624EFiberPrem6Router', 'object' => 'snFESX624EFiberPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.1' => array('name' => 'snFESX624EFiberPlus1XG', 'object' => 'snFESX624EFiberPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.1.1' => array('name' => 'snFESX624EFiberPlus1XGSwitch', 'object' => 'snFESX624EFiberPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.1.2' => array('name' => 'snFESX624EFiberPlus1XGRouter', 'object' => 'snFESX624EFiberPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.2' => array('name' => 'snFESX624EFiberPlus1XGPrem', 'object' => 'snFESX624EFiberPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.2.1' => array('name' => 'snFESX624EFiberPlus1XGPremSwitch', 'object' => 'snFESX624EFiberPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.2.2' => array('name' => 'snFESX624EFiberPlus1XGPremRouter', 'object' => 'snFESX624EFiberPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.2.2.3' => array('name' => 'snFESX624EFiberPlus1XGPrem6Router', 'object' => 'snFESX624EFiberPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.1' => array('name' => 'snFESX624EFiberPlus2XG', 'object' => 'snFESX624EFiberPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.1.1' => array('name' => 'snFESX624EFiberPlus2XGSwitch', 'object' => 'snFESX624EFiberPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.1.2' => array('name' => 'snFESX624EFiberPlus2XGRouter', 'object' => 'snFESX624EFiberPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.2' => array('name' => 'snFESX624EFiberPlus2XGPrem', 'object' => 'snFESX624EFiberPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.2.1' => array('name' => 'snFESX624EFiberPlus2XGPremSwitch', 'object' => 'snFESX624EFiberPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.2.2' => array('name' => 'snFESX624EFiberPlus2XGPremRouter', 'object' => 'snFESX624EFiberPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.12.3.2.3' => array('name' => 'snFESX624EFiberPlus2XGPrem6Router', 'object' => 'snFESX624EFiberPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.1' => array('name' => 'snFESX648E', 'object' => 'snFESX648E'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.1.1' => array('name' => 'snFESX648ESwitch', 'object' => 'snFESX648ESwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.1.2' => array('name' => 'snFESX648ERouter', 'object' => 'snFESX648ERouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.2' => array('name' => 'snFESX648EPrem', 'object' => 'snFESX648EPrem'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.2.1' => array('name' => 'snFESX648EPremSwitch', 'object' => 'snFESX648EPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.2.2' => array('name' => 'snFESX648EPremRouter', 'object' => 'snFESX648EPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.1.2.3' => array('name' => 'snFESX648EPrem6Router', 'object' => 'snFESX648EPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.1' => array('name' => 'snFESX648EPlus1XG', 'object' => 'snFESX648EPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.1.1' => array('name' => 'snFESX648EPlus1XGSwitch', 'object' => 'snFESX648EPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.1.2' => array('name' => 'snFESX648EPlus1XGRouter', 'object' => 'snFESX648EPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.2' => array('name' => 'snFESX648EPlus1XGPrem', 'object' => 'snFESX648EPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.2.1' => array('name' => 'snFESX648EPlus1XGPremSwitch', 'object' => 'snFESX648EPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.2.2' => array('name' => 'snFESX648EPlus1XGPremRouter', 'object' => 'snFESX648EPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.2.2.3' => array('name' => 'snFESX648EPlus1XGPrem6Router', 'object' => 'snFESX648EPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.1' => array('name' => 'snFESX648EPlus2XG', 'object' => 'snFESX648EPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.1.1' => array('name' => 'snFESX648EPlus2XGSwitch', 'object' => 'snFESX648EPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.1.2' => array('name' => 'snFESX648EPlus2XGRouter', 'object' => 'snFESX648EPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.2' => array('name' => 'snFESX648EPlus2XGPrem', 'object' => 'snFESX648EPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.2.1' => array('name' => 'snFESX648EPlus2XGPremSwitch', 'object' => 'snFESX648EPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.2.2' => array('name' => 'snFESX648EPlus2XGPremRouter', 'object' => 'snFESX648EPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.13.3.2.3' => array('name' => 'snFESX648EPlus2XGPrem6Router', 'object' => 'snFESX648EPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.1' => array('name' => 'FES 48G', 'object' => 'snFESX448'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.1.1' => array('name' => 'FESX448', 'object' => 'snFESX448Switch'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.1.2' => array('name' => 'FESX448', 'object' => 'snFESX448Router'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.2' => array('name' => 'FES 48G-PREM', 'object' => 'snFESX448Prem'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.2.1' => array('name' => 'FESX448-PREM', 'object' => 'snFESX448PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.2.1.2.2' => array('name' => 'FESX448-PREM', 'object' => 'snFESX448PremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.1' => array('name' => 'FES 48G + 1 10G', 'object' => 'snFESX448Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.1.1' => array('name' => 'FESX448+1XG', 'object' => 'snFESX448Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.1.2' => array('name' => 'FESX448+1XG', 'object' => 'snFESX448Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.2' => array('name' => 'FES 48G + 1 10G-PREM', 'object' => 'snFESX448Plus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.2.1' => array('name' => 'FESX448+1XG-PREM', 'object' => 'snFESX448Plus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.2.2.2.2' => array('name' => 'FESX448+1XG-PREM', 'object' => 'snFESX448Plus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.1' => array('name' => 'FES 48G + 2 10G', 'object' => 'snFESX448Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.1.1' => array('name' => 'FESX448+2XG', 'object' => 'snFESX448Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.1.2' => array('name' => 'FESX448+2XG', 'object' => 'snFESX448Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.2' => array('name' => 'FES 48G + 2 10G-PREM', 'object' => 'snFESX448Plus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.2.1' => array('name' => 'FESX448+2XG-PREM', 'object' => 'snFESX448Plus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.2.3.2.2' => array('name' => 'FESX448+2XG-PREM', 'object' => 'snFESX448Plus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.1' => array('name' => 'FESFiber 24G', 'object' => 'snFESX424Fiber'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.1.1' => array('name' => 'FESX424Fiber', 'object' => 'snFESX424FiberSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.1.2' => array('name' => 'FESX424Fiber', 'object' => 'snFESX424FiberRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.2' => array('name' => 'FESFiber 24G-PREM', 'object' => 'snFESX424FiberPrem'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.2.1' => array('name' => 'FESX424Fiber-PREM', 'object' => 'snFESX424FiberPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.1.2.2' => array('name' => 'FESX424Fiber-PREM', 'object' => 'snFESX424FiberPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.1' => array('name' => 'FESFiber 24G + 1 10G', 'object' => 'snFESX424FiberPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.1.1' => array('name' => 'FESX424Fiber+1XG', 'object' => 'snFESX424FiberPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.1.2' => array('name' => 'FESX424Fiber+1XG', 'object' => 'snFESX424FiberPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.2' => array('name' => 'FESFiber 24G + 1 10G-PREM', 'object' => 'snFESX424FiberPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.2.1' => array('name' => 'FESX424Fiber+1XG-PREM', 'object' => 'snFESX424FiberPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.2.2.2' => array('name' => 'FESX424Fiber+1XG-PREM', 'object' => 'snFESX424FiberPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.1' => array('name' => 'FESFiber 24G + 2 10G', 'object' => 'snFESX424FiberPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.1.1' => array('name' => 'FESX424Fiber+2XG', 'object' => 'snFESX424FiberPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.1.2' => array('name' => 'FESX424Fiber+2XG', 'object' => 'snFESX424FiberPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.2' => array('name' => 'FESFiber 24G + 2 10G-PREM', 'object' => 'snFESX424FiberPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.2.1' => array('name' => 'FESX424Fiber+2XG-PREM', 'object' => 'snFESX424FiberPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.3.3.2.2' => array('name' => 'FESX424Fiber+2XG-PREM', 'object' => 'snFESX424FiberPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.1' => array('name' => 'FESFiber 48G', 'object' => 'snFESX448Fiber'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.1.1' => array('name' => 'FESX448Fiber', 'object' => 'snFESX448FiberSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.1.2' => array('name' => 'FESX448Fiber', 'object' => 'snFESX448FiberRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.2' => array('name' => 'FESFiber 48G-PREM', 'object' => 'snFESX448FiberPrem'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.2.1' => array('name' => 'FESX448Fiber-PREM', 'object' => 'snFESX448FiberPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.1.2.2' => array('name' => 'FESX448Fiber-PREM', 'object' => 'snFESX448FiberPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.1' => array('name' => 'FESFiber 48G + 1 10G', 'object' => 'snFESX448FiberPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.1.1' => array('name' => 'FESX448Fiber+1XG', 'object' => 'snFESX448FiberPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.1.2' => array('name' => 'FESX448Fiber+1XG', 'object' => 'snFESX448FiberPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.2' => array('name' => 'FESFiber 48G + 1 10G-PREM', 'object' => 'snFESX448FiberPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.2.1' => array('name' => 'FESX448Fiber+1XG-PREM', 'object' => 'snFESX448FiberPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.2.2.2' => array('name' => 'FESX448Fiber+1XG-PREM', 'object' => 'snFESX448FiberPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.1' => array('name' => 'FESFiber 48G + 2 10G', 'object' => 'snFESX448FiberPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.1.1' => array('name' => 'FESX448Fiber+2XG', 'object' => 'snFESX448FiberPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.1.2' => array('name' => 'FESX448+2XG', 'object' => 'snFESX448FiberPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.2' => array('name' => 'FESFiber 48G + 2 10G-PREM', 'object' => 'snFESX448FiberPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.2.1' => array('name' => 'FESX448Fiber+2XG-PREM', 'object' => 'snFESX448FiberPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.4.3.2.2' => array('name' => 'FESX448Fiber+2XG-PREM', 'object' => 'snFESX448FiberPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.1' => array('name' => 'snFESX424POE', 'object' => 'snFESX424POE'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.1.1' => array('name' => 'snFESX424POESwitch', 'object' => 'snFESX424POESwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.1.2' => array('name' => 'snFESX424POERouter', 'object' => 'snFESX424POERouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.2' => array('name' => 'snFESX424POEPrem', 'object' => 'snFESX424POEPrem'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.2.1' => array('name' => 'snFESX424POEPremSwitch', 'object' => 'snFESX424POEPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.1.2.2' => array('name' => 'snFESX424POEPremRouter', 'object' => 'snFESX424POEPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.1' => array('name' => 'snFESX424POEPlus1XG', 'object' => 'snFESX424POEPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.1.1' => array('name' => 'snFESX424POEPlus1XGSwitch', 'object' => 'snFESX424POEPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.1.2' => array('name' => 'snFESX424POEPlus1XGRouter', 'object' => 'snFESX424POEPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.2' => array('name' => 'snFESX424POEPlus1XGPrem', 'object' => 'snFESX424POEPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.2.1' => array('name' => 'snFESX424POEPlus1XGPremSwitch', 'object' => 'snFESX424POEPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.2.2.2' => array('name' => 'snFESX424POEPlus1XGPremRouter', 'object' => 'snFESX424POEPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.1' => array('name' => 'snFESX424POEPlus2XG', 'object' => 'snFESX424POEPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.1.1' => array('name' => 'snFESX424POEPlus2XGSwitch', 'object' => 'snFESX424POEPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.1.2' => array('name' => 'snFESX424POEPlus2XGRouter', 'object' => 'snFESX424POEPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.2' => array('name' => 'snFESX424POEPlus2XGPrem', 'object' => 'snFESX424POEPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.2.1' => array('name' => 'snFESX424POEPlus2XGPremSwitch', 'object' => 'snFESX424POEPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.5.3.2.2' => array('name' => 'snFESX424POEPlus2XGPremRouter', 'object' => 'snFESX424POEPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.1' => array('name' => 'FastIron Edge V6 Switch(FES) 24G', 'object' => 'snFESX624'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.1.1' => array('name' => 'FESX624', 'object' => 'snFESX624Switch'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.1.2' => array('name' => 'FESX624', 'object' => 'snFESX624Router'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.2' => array('name' => 'FastIron Edge V6 Switch(FES) 24G-PREM', 'object' => 'snFESX624Prem'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.2.1' => array('name' => 'FESX624-PREM', 'object' => 'snFESX624PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.2.2' => array('name' => 'FESX624-PREM', 'object' => 'snFESX624PremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.1.2.3' => array('name' => 'snFESX624Prem6Router', 'object' => 'snFESX624Prem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.1' => array('name' => 'FastIron Edge V6 Switch(FES) 24G + 1 10G', 'object' => 'snFESX624Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.1.1' => array('name' => 'FESX624+1XG', 'object' => 'snFESX624Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.1.2' => array('name' => 'FESX624+1XG', 'object' => 'snFESX624Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.2' => array('name' => 'FastIron Edge V6 Switch(FES) 24G + 1 10G-PREM', 'object' => 'snFESX624Plus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.2.1' => array('name' => 'FESX624+1XG-PREM', 'object' => 'snFESX624Plus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.2.2' => array('name' => 'FESX624+1XG-PREM', 'object' => 'snFESX624Plus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.2.2.3' => array('name' => 'snFESX624Plus1XGPrem6Router', 'object' => 'snFESX624Plus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.1' => array('name' => 'FastIron Edge V6 Switch(FES) 24G + 2 10G', 'object' => 'snFESX624Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.1.1' => array('name' => 'FESX624+2XG', 'object' => 'snFESX624Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.1.2' => array('name' => 'FESX624+2XG', 'object' => 'snFESX624Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.2' => array('name' => 'FastIron Edge V6 Switch(FES) 24G + 2 10G-PREM', 'object' => 'snFESX624Plus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.2.1' => array('name' => 'FESX624+2XG-PREM', 'object' => 'snFESX624Plus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.2.2' => array('name' => 'FESX624+2XG-PREM', 'object' => 'snFESX624Plus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.6.3.2.3' => array('name' => 'snFESX624Plus2XGPrem6Router', 'object' => 'snFESX624Plus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.1' => array('name' => 'FastIron Edge V6 Switch(FES) 48G', 'object' => 'snFESX648'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.1.1' => array('name' => 'FESX648', 'object' => 'snFESX648Switch'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.1.2' => array('name' => 'FESX648', 'object' => 'snFESX648Router'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.2' => array('name' => 'FastIron Edge V6 Switch(FES) 48G-PREM', 'object' => 'snFESX648Prem'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.2.1' => array('name' => 'FESX648-PREM', 'object' => 'snFESX648PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.2.2' => array('name' => 'FESX648-PREM', 'object' => 'snFESX648PremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.7.1.2.3' => array('name' => 'snFESX648Prem6Router', 'object' => 'snFESX648Prem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.1' => array('name' => 'FastIron Edge V6 Switch(FES) 48G + 1 10G', 'object' => 'snFESX648Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.1.1' => array('name' => 'FESX648+1XG', 'object' => 'snFESX648Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.1.2' => array('name' => 'FESX648+1XG', 'object' => 'snFESX648Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.2' => array('name' => 'FastIron Edge V6 Switch(FES) 48G + 1 10G-PREM', 'object' => 'snFESX648Plus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.2.1' => array('name' => 'FESX648+1XG-PREM', 'object' => 'snFESX648Plus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.2.2' => array('name' => 'FESX648+1XG-PREM', 'object' => 'snFESX648Plus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.7.2.2.3' => array('name' => 'snFESX648Plus1XGPrem6Router', 'object' => 'snFESX648Plus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.1' => array('name' => 'FastIron Edge V6 Switch(FES) 48G + 2 10G', 'object' => 'snFESX648Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.1.1' => array('name' => 'FESX648+2XG', 'object' => 'snFESX648Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.1.2' => array('name' => 'FESX648+2XG', 'object' => 'snFESX648Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.2' => array('name' => 'FastIron Edge V6 Switch(FES) 48G + 2 10G-PREM', 'object' => 'snFESX648Plus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.2.1' => array('name' => 'FESX648+2XG-PREM', 'object' => 'snFESX648Plus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.2.2' => array('name' => 'FESX648+2XG-PREM', 'object' => 'snFESX648Plus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.7.3.2.3' => array('name' => 'snFESX648Plus2XGPrem6Router', 'object' => 'snFESX648Plus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.1' => array('name' => 'FastIron V6 Edge Switch(FES)Fiber 24G', 'object' => 'snFESX624Fiber'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.1.1' => array('name' => 'FESX624Fiber', 'object' => 'snFESX624FiberSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.1.2' => array('name' => 'FESX624Fiber', 'object' => 'snFESX624FiberRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 24G-PREM', 'object' => 'snFESX624FiberPrem'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.2.1' => array('name' => 'FESX624Fiber-PREM', 'object' => 'snFESX624FiberPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.2.2' => array('name' => 'FESX624Fiber-PREM', 'object' => 'snFESX624FiberPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.1.2.3' => array('name' => 'snFESX624FiberPrem6Router', 'object' => 'snFESX624FiberPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.1' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 24G + 1 10G', 'object' => 'snFESX624FiberPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.1.1' => array('name' => 'FESX624Fiber+1XG', 'object' => 'snFESX624FiberPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.1.2' => array('name' => 'FESX624Fiber+1XG', 'object' => 'snFESX624FiberPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 24G + 1 10G-PREM', 'object' => 'snFESX624FiberPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.2.1' => array('name' => 'FESX624Fiber+1XG-PREM', 'object' => 'snFESX624FiberPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.2.2' => array('name' => 'FESX624Fiber+1XG-PREM', 'object' => 'snFESX624FiberPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.2.2.3' => array('name' => 'snFESX624FiberPlus1XGPrem6Router', 'object' => 'snFESX624FiberPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.1' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 24G + 2 10G', 'object' => 'snFESX624FiberPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.1.1' => array('name' => 'FESX624Fiber+2XG', 'object' => 'snFESX624FiberPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.1.2' => array('name' => 'FESX624Fiber+2XG', 'object' => 'snFESX624FiberPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 24G + 2 10G-PREM', 'object' => 'snFESX624FiberPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.2.1' => array('name' => 'FESX624Fiber+2XG-PREM', 'object' => 'snFESX624FiberPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.2.2' => array('name' => 'FESX624Fiber+2XG-PREM', 'object' => 'snFESX624FiberPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.8.3.2.3' => array('name' => 'snFESX624FiberPlus2XGPrem6Router', 'object' => 'snFESX624FiberPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.1' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G', 'object' => 'snFESX648Fiber'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.1.1' => array('name' => 'FESX648Fiber', 'object' => 'snFESX648FiberSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.1.2' => array('name' => 'FESX648Fiber', 'object' => 'snFESX648FiberRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G-PREM', 'object' => 'snFESX648FiberPrem'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.2.1' => array('name' => 'FESX648Fiber-PREM', 'object' => 'snFESX648FiberPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.2.2' => array('name' => 'FESX648Fiber-PREM', 'object' => 'snFESX648FiberPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.1.2.3' => array('name' => 'snFESX648FiberPrem6Router', 'object' => 'snFESX648FiberPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.1' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G + 1 10G', 'object' => 'snFESX648FiberPlus1XG'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.1.1' => array('name' => 'FESX648Fiber+1XG', 'object' => 'snFESX648FiberPlus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.1.2' => array('name' => 'FESX648Fiber+1XG', 'object' => 'snFESX648FiberPlus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G + 1 10G-PREM', 'object' => 'snFESX648FiberPlus1XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.2.1' => array('name' => 'FESX648Fiber+1XG-PREM', 'object' => 'snFESX648FiberPlus1XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.2.2' => array('name' => 'FESX648Fiber+1XG-PREM', 'object' => 'snFESX648FiberPlus1XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.2.2.3' => array('name' => 'snFESX648FiberPlus1XGPrem6Router', 'object' => 'snFESX648FiberPlus1XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.1' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G + 2 10G', 'object' => 'snFESX648FiberPlus2XG'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.1.1' => array('name' => 'FESX648Fiber+2XG', 'object' => 'snFESX648FiberPlus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.1.2' => array('name' => 'FESX648+2XG', 'object' => 'snFESX648FiberPlus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.2' => array('name' => 'FastIron Edge V6 Switch(FES)Fiber 48G + 2 10G-PREM', 'object' => 'snFESX648FiberPlus2XGPrem'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.2.1' => array('name' => 'FESX648Fiber+2XG-PREM', 'object' => 'snFESX648FiberPlus2XGPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.2.2' => array('name' => 'FESX648Fiber+2XG-PREM', 'object' => 'snFESX648FiberPlus2XGPremRouter'),
'.1.3.6.1.4.1.1991.1.3.34.9.3.2.3' => array('name' => 'snFESX648FiberPlus2XGPrem6Router', 'object' => 'snFESX648FiberPlus2XGPrem6Router'),
'.1.3.6.1.4.1.1991.1.3.35.1.1.1' => array('name' => 'FWSX24G', 'object' => 'snFWSX424'),
'.1.3.6.1.4.1.1991.1.3.35.1.1.1.1' => array('name' => 'FWSX424', 'object' => 'snFWSX424Switch'),
'.1.3.6.1.4.1.1991.1.3.35.1.1.1.2' => array('name' => 'FWSX424', 'object' => 'snFWSX424Router'),
'.1.3.6.1.4.1.1991.1.3.35.1.2.1' => array('name' => 'FWSX24G + 1 10G', 'object' => 'snFWSX424Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.35.1.2.1.1' => array('name' => 'FWSX424+1XG', 'object' => 'snFWSX424Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.35.1.2.1.2' => array('name' => 'FWSX424+1XG', 'object' => 'snFWSX424Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.35.1.3.1' => array('name' => 'FWSX24G + 2 10G', 'object' => 'snFWSX424Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.35.1.3.1.1' => array('name' => 'FWSX424+2XG', 'object' => 'snFWSX424Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.35.1.3.1.2' => array('name' => 'FWSX424+2XG', 'object' => 'snFWSX424Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.35.2.1.1' => array('name' => 'FWSX48G', 'object' => 'snFWSX448'),
'.1.3.6.1.4.1.1991.1.3.35.2.1.1.1' => array('name' => 'FWSX448', 'object' => 'snFWSX448Switch'),
'.1.3.6.1.4.1.1991.1.3.35.2.1.1.2' => array('name' => 'FWSX448', 'object' => 'snFWSX448Router'),
'.1.3.6.1.4.1.1991.1.3.35.2.2.1' => array('name' => 'FWSX48G + 1 10G', 'object' => 'snFWSX448Plus1XG'),
'.1.3.6.1.4.1.1991.1.3.35.2.2.1.1' => array('name' => 'FWSX448+1XG', 'object' => 'snFWSX448Plus1XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.35.2.2.1.2' => array('name' => 'FWSX448+1XG', 'object' => 'snFWSX448Plus1XGRouter'),
'.1.3.6.1.4.1.1991.1.3.35.2.3.1' => array('name' => 'FWSX448G+2XG', 'object' => 'snFWSX448Plus2XG'),
'.1.3.6.1.4.1.1991.1.3.35.2.3.1.1' => array('name' => 'FWSX448+2XG', 'object' => 'snFWSX448Plus2XGSwitch'),
'.1.3.6.1.4.1.1991.1.3.35.2.3.1.2' => array('name' => 'FWSX448+2XG', 'object' => 'snFWSX448Plus2XGRouter'),
'.1.3.6.1.4.1.1991.1.3.36.1' => array('name' => 'FastIron SuperX', 'object' => 'snFastIronSuperX'),
'.1.3.6.1.4.1.1991.1.3.36.1.1' => array('name' => 'FastIron SuperX Switch', 'object' => 'snFastIronSuperXSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.1.2' => array('name' => 'FastIron SuperX Router', 'object' => 'snFastIronSuperXRouter'),
'.1.3.6.1.4.1.1991.1.3.36.1.3' => array('name' => 'FastIron SuperX Base L3 Switch', 'object' => 'snFastIronSuperXBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.10' => array('name' => 'FastIron SuperX 800 V6 Premium', 'object' => 'snFastIronSuperX800V6Prem'),
'.1.3.6.1.4.1.1991.1.3.36.10.1' => array('name' => 'FastIron SuperX 800 Premium V6 Switch', 'object' => 'snFastIronSuperX800V6PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.10.2' => array('name' => 'FastIron SuperX 800 Premium V6 Router', 'object' => 'snFastIronSuperX800V6PremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.10.3' => array('name' => 'FastIron SuperX 800 Premium V6 Base L3 Switch', 'object' => 'snFastIronSuperX800V6PremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.10.4' => array('name' => 'snFastIronSuperX800V6Prem6Router', 'object' => 'snFastIronSuperX800V6Prem6Router'),
'.1.3.6.1.4.1.1991.1.3.36.11' => array('name' => 'FastIron SuperX 1600 V6 ', 'object' => 'snFastIronSuperX1600V6'),
'.1.3.6.1.4.1.1991.1.3.36.11.1' => array('name' => 'FastIron SuperX 1600 V6 Switch', 'object' => 'snFastIronSuperX1600V6Switch'),
'.1.3.6.1.4.1.1991.1.3.36.11.2' => array('name' => 'FastIron SuperX 1600 V6 Router', 'object' => 'snFastIronSuperX1600V6Router'),
'.1.3.6.1.4.1.1991.1.3.36.11.3' => array('name' => 'FastIron SuperX 1600 V6 Base L3 Switch', 'object' => 'snFastIronSuperX1600V6BaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.12' => array('name' => 'FastIron SuperX 1600 Premium V6', 'object' => 'snFastIronSuperX1600V6Prem'),
'.1.3.6.1.4.1.1991.1.3.36.12.1' => array('name' => 'FastIron SuperX 1600 Premium V6 Switch', 'object' => 'snFastIronSuperX1600V6PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.12.2' => array('name' => 'FastIron SuperX 1600 Premium V6 Router', 'object' => 'snFastIronSuperX1600V6PremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.12.3' => array('name' => 'FastIron SuperX 1600 Premium V6 Base L3 Switch', 'object' => 'snFastIronSuperX1600V6PremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.12.4' => array('name' => 'snFastIronSuperX1600V6Prem6Router', 'object' => 'snFastIronSuperX1600V6Prem6Router'),
'.1.3.6.1.4.1.1991.1.3.36.2' => array('name' => 'FastIron SuperX Premium', 'object' => 'snFastIronSuperXPrem'),
'.1.3.6.1.4.1.1991.1.3.36.2.1' => array('name' => 'FastIron SuperX Premium Switch', 'object' => 'snFastIronSuperXPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.2.2' => array('name' => 'FastIron SuperX Premium Router', 'object' => 'snFastIronSuperXPremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.2.3' => array('name' => 'FastIron SuperX Premium Base L3 Switch', 'object' => 'snFastIronSuperXPremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.3' => array('name' => 'FastIron SuperX 800 ', 'object' => 'snFastIronSuperX800'),
'.1.3.6.1.4.1.1991.1.3.36.3.1' => array('name' => 'FastIron SuperX 800 Switch', 'object' => 'snFastIronSuperX800Switch'),
'.1.3.6.1.4.1.1991.1.3.36.3.2' => array('name' => 'FastIron SuperX 800 Router', 'object' => 'snFastIronSuperX800Router'),
'.1.3.6.1.4.1.1991.1.3.36.3.3' => array('name' => 'FastIron SuperX 800 Base L3 Switch', 'object' => 'snFastIronSuperX800BaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.4' => array('name' => 'FastIron SuperX 800 Premium', 'object' => 'snFastIronSuperX800Prem'),
'.1.3.6.1.4.1.1991.1.3.36.4.1' => array('name' => 'FastIron SuperX 800 Premium Switch', 'object' => 'snFastIronSuperX800PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.4.2' => array('name' => 'FastIron SuperX 800 Premium Router', 'object' => 'snFastIronSuperX800PremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.4.3' => array('name' => 'FastIron SuperX 800 Premium Base L3 Switch', 'object' => 'snFastIronSuperX800PremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.5' => array('name' => 'FastIron SuperX 1600 ', 'object' => 'snFastIronSuperX1600'),
'.1.3.6.1.4.1.1991.1.3.36.5.1' => array('name' => 'FastIron SuperX 1600 Switch', 'object' => 'snFastIronSuperX1600Switch'),
'.1.3.6.1.4.1.1991.1.3.36.5.2' => array('name' => 'FastIron SuperX 1600 Router', 'object' => 'snFastIronSuperX1600Router'),
'.1.3.6.1.4.1.1991.1.3.36.5.3' => array('name' => 'FastIron SuperX 1600 Base L3 Switch', 'object' => 'snFastIronSuperX1600BaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.6' => array('name' => 'FastIron SuperX 1600 Premium', 'object' => 'snFastIronSuperX1600Prem'),
'.1.3.6.1.4.1.1991.1.3.36.6.1' => array('name' => 'FastIron SuperX 1600 Premium Switch', 'object' => 'snFastIronSuperX1600PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.6.2' => array('name' => 'FastIron SuperX 1600 Premium Router', 'object' => 'snFastIronSuperX1600PremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.6.3' => array('name' => 'FastIron SuperX 1600 Premium Base L3 Switch', 'object' => 'snFastIronSuperX1600PremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.7' => array('name' => 'FastIron SuperX V6 ', 'object' => 'snFastIronSuperXV6'),
'.1.3.6.1.4.1.1991.1.3.36.7.1' => array('name' => 'FastIron SuperX V6 Switch', 'object' => 'snFastIronSuperXV6Switch'),
'.1.3.6.1.4.1.1991.1.3.36.7.2' => array('name' => 'FastIron SuperX V6 Router', 'object' => 'snFastIronSuperXV6Router'),
'.1.3.6.1.4.1.1991.1.3.36.7.3' => array('name' => 'FastIron SuperX V6 Base L3 Switch', 'object' => 'snFastIronSuperXV6BaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.8' => array('name' => 'FastIron SuperX V6 Premium', 'object' => 'snFastIronSuperXV6Prem'),
'.1.3.6.1.4.1.1991.1.3.36.8.1' => array('name' => 'FastIron SuperX V6 Premium Switch', 'object' => 'snFastIronSuperXV6PremSwitch'),
'.1.3.6.1.4.1.1991.1.3.36.8.2' => array('name' => 'FastIron SuperX V6 Premium Router', 'object' => 'snFastIronSuperXV6PremRouter'),
'.1.3.6.1.4.1.1991.1.3.36.8.3' => array('name' => 'FastIron SuperX V6 Premium Base L3 Switch', 'object' => 'snFastIronSuperXV6PremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.36.8.4' => array('name' => 'snFastIronSuperXV6Prem6Router', 'object' => 'snFastIronSuperXV6Prem6Router'),
'.1.3.6.1.4.1.1991.1.3.36.9' => array('name' => 'FastIron SuperX 800 V6 ', 'object' => 'snFastIronSuperX800V6'),
'.1.3.6.1.4.1.1991.1.3.36.9.1' => array('name' => 'FastIron SuperX 800 V6 Switch', 'object' => 'snFastIronSuperX800V6Switch'),
'.1.3.6.1.4.1.1991.1.3.36.9.2' => array('name' => 'FastIron SuperX 800 V6 Router', 'object' => 'snFastIronSuperX800V6Router'),
'.1.3.6.1.4.1.1991.1.3.36.9.3' => array('name' => 'FastIron SuperX 800 V6 Base L3 Switch', 'object' => 'snFastIronSuperX800V6BaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.37.1' => array('name' => 'BigIron SuperX', 'object' => 'snBigIronSuperX'),
'.1.3.6.1.4.1.1991.1.3.37.1.1' => array('name' => 'BigIron SuperX Switch', 'object' => 'snBigIronSuperXSwitch'),
'.1.3.6.1.4.1.1991.1.3.37.1.2' => array('name' => 'BigIron SuperX Router', 'object' => 'snBigIronSuperXRouter'),
'.1.3.6.1.4.1.1991.1.3.37.1.3' => array('name' => 'BigIron SuperX Base L3 Switch', 'object' => 'snBigIronSuperXBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.38.1' => array('name' => 'TurboIron SuperX', 'object' => 'snTurboIronSuperX'),
'.1.3.6.1.4.1.1991.1.3.38.1.1' => array('name' => 'TurboIron SuperX Switch', 'object' => 'snTurboIronSuperXSwitch'),
'.1.3.6.1.4.1.1991.1.3.38.1.2' => array('name' => 'TurboIron SuperX Router', 'object' => 'snTurboIronSuperXRouter'),
'.1.3.6.1.4.1.1991.1.3.38.1.3' => array('name' => 'TurboIron SuperX Base L3 Switch', 'object' => 'snTurboIronSuperXBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.38.2' => array('name' => 'TurboIron SuperX Premium', 'object' => 'snTurboIronSuperXPrem'),
'.1.3.6.1.4.1.1991.1.3.38.2.1' => array('name' => 'TurboIron SuperX Premium Switch', 'object' => 'snTurboIronSuperXPremSwitch'),
'.1.3.6.1.4.1.1991.1.3.38.2.2' => array('name' => 'TurboIron SuperX Premium Router', 'object' => 'snTurboIronSuperXPremRouter'),
'.1.3.6.1.4.1.1991.1.3.38.2.3' => array('name' => 'TurboIron SuperX Premium Base L3 Switch', 'object' => 'snTurboIronSuperXPremBaseL3Switch'),
'.1.3.6.1.4.1.1991.1.3.39.1' => array('name' => 'snNetIronIMR', 'object' => 'snNetIronIMR'),
'.1.3.6.1.4.1.1991.1.3.39.1.2' => array('name' => 'NetIron IMR', 'object' => 'snNIIMRRouter'),
'.1.3.6.1.4.1.1991.1.3.4' => array('name' => 'snTurboIron', 'object' => 'snTurboIron'),
'.1.3.6.1.4.1.1991.1.3.4.1' => array('name' => 'Stackable TurboIron', 'object' => 'snTISwitch'),
'.1.3.6.1.4.1.1991.1.3.4.2' => array('name' => 'Stackable TurboIron', 'object' => 'snTIRouter'),
'.1.3.6.1.4.1.1991.1.3.40.1' => array('name' => 'snBigIronRX16', 'object' => 'snBigIronRX16'),
'.1.3.6.1.4.1.1991.1.3.40.1.1' => array('name' => 'BigIron RX16', 'object' => 'snBIRX16Switch'),
'.1.3.6.1.4.1.1991.1.3.40.1.2' => array('name' => 'BigIron RX16', 'object' => 'snBIRX16Router'),
'.1.3.6.1.4.1.1991.1.3.40.2' => array('name' => 'snBigIronRX8', 'object' => 'snBigIronRX8'),
'.1.3.6.1.4.1.1991.1.3.40.2.1' => array('name' => 'BigIron RX8', 'object' => 'snBIRX8Switch'),
'.1.3.6.1.4.1.1991.1.3.40.2.2' => array('name' => 'BigIron RX8', 'object' => 'snBIRX8Router'),
'.1.3.6.1.4.1.1991.1.3.40.3' => array('name' => 'snBigIronRX4', 'object' => 'snBigIronRX4'),
'.1.3.6.1.4.1.1991.1.3.40.3.1' => array('name' => 'BigIron RX4', 'object' => 'snBIRX4Switch'),
'.1.3.6.1.4.1.1991.1.3.40.3.2' => array('name' => 'BigIron RX4', 'object' => 'snBIRX4Router'),
'.1.3.6.1.4.1.1991.1.3.40.4' => array('name' => 'snBigIronRX32', 'object' => 'snBigIronRX32'),
'.1.3.6.1.4.1.1991.1.3.40.4.1' => array('name' => 'BigIron RX32', 'object' => 'snBIRX32Switch'),
'.1.3.6.1.4.1.1991.1.3.40.4.2' => array('name' => 'BigIron RX32', 'object' => 'snBIRX32Router'),
'.1.3.6.1.4.1.1991.1.3.41.1' => array('name' => 'snNetIronXMR16000', 'object' => 'snNetIronXMR16000'),
'.1.3.6.1.4.1.1991.1.3.41.1.2' => array('name' => 'NetIron XMR16000', 'object' => 'snNIXMR16000Router'),
'.1.3.6.1.4.1.1991.1.3.41.2' => array('name' => 'snNetIronXMR8000', 'object' => 'snNetIronXMR8000'),
'.1.3.6.1.4.1.1991.1.3.41.2.2' => array('name' => 'NetIron XMR8000', 'object' => 'snNIXMR8000Router'),
'.1.3.6.1.4.1.1991.1.3.41.3' => array('name' => 'snNetIronXMR4000', 'object' => 'snNetIronXMR4000'),
'.1.3.6.1.4.1.1991.1.3.41.3.2' => array('name' => 'NetIron XMR4000', 'object' => 'snNIXMR4000Router'),
'.1.3.6.1.4.1.1991.1.3.41.4' => array('name' => 'snNetIronXMR32000', 'object' => 'snNetIronXMR32000'),
'.1.3.6.1.4.1.1991.1.3.41.4.2' => array('name' => 'NetIron XMR32000', 'object' => 'snNIXMR32000Router'),
'.1.3.6.1.4.1.1991.1.3.42.10.1' => array('name' => 'SecureIronTM 100', 'object' => 'snSecureIronTM100'),
'.1.3.6.1.4.1.1991.1.3.42.10.1.1' => array('name' => 'SecureIronTM 100 Switch', 'object' => 'snSecureIronTM100Switch'),
'.1.3.6.1.4.1.1991.1.3.42.10.1.2' => array('name' => 'SecureIronTM 100 Router', 'object' => 'snSecureIronTM100Router'),
'.1.3.6.1.4.1.1991.1.3.42.10.2' => array('name' => 'SecureIronTM 300', 'object' => 'snSecureIronTM300'),
'.1.3.6.1.4.1.1991.1.3.42.10.2.1' => array('name' => 'SecureIronTM 300 Switch', 'object' => 'snSecureIronTM300Switch'),
'.1.3.6.1.4.1.1991.1.3.42.10.2.2' => array('name' => 'SecureIronTM 300 Router', 'object' => 'snSecureIronTM300Router'),
'.1.3.6.1.4.1.1991.1.3.42.9.1' => array('name' => 'SecureIronLS 100', 'object' => 'snSecureIronLS100'),
'.1.3.6.1.4.1.1991.1.3.42.9.1.1' => array('name' => 'SecureIronLS 100 Switch', 'object' => 'snSecureIronLS100Switch'),
'.1.3.6.1.4.1.1991.1.3.42.9.1.2' => array('name' => 'SecureIronLS 100 Router', 'object' => 'snSecureIronLS100Router'),
'.1.3.6.1.4.1.1991.1.3.42.9.2' => array('name' => 'SecureIronLS 300', 'object' => 'snSecureIronLS300'),
'.1.3.6.1.4.1.1991.1.3.42.9.2.1' => array('name' => 'SecureIronLS 300 Switch', 'object' => 'snSecureIronLS300Switch'),
'.1.3.6.1.4.1.1991.1.3.42.9.2.2' => array('name' => 'SecureIronLS 300 Router', 'object' => 'snSecureIronLS300Router'),
'.1.3.6.1.4.1.1991.1.3.44.1' => array('name' => 'snNetIronMLX16', 'object' => 'snNetIronMLX16'),
'.1.3.6.1.4.1.1991.1.3.44.1.2' => array('name' => 'NetIron MLX-16', 'object' => 'snNetIronMLX16Router'),
'.1.3.6.1.4.1.1991.1.3.44.2' => array('name' => 'snNetIronMLX8', 'object' => 'snNetIronMLX8'),
'.1.3.6.1.4.1.1991.1.3.44.2.2' => array('name' => 'NetIron MLX-8', 'object' => 'snNetIronMLX8Router'),
'.1.3.6.1.4.1.1991.1.3.44.3' => array('name' => 'snNetIronMLX4', 'object' => 'snNetIronMLX4'),
'.1.3.6.1.4.1.1991.1.3.44.3.2' => array('name' => 'NetIron MLX-4', 'object' => 'snNetIronMLX4Router'),
'.1.3.6.1.4.1.1991.1.3.44.4' => array('name' => 'snNetIronMLX32', 'object' => 'snNetIronMLX32'),
'.1.3.6.1.4.1.1991.1.3.44.4.2' => array('name' => 'NetIron MLX-32', 'object' => 'snNetIronMLX32Router'),
'.1.3.6.1.4.1.1991.1.3.45.1.1.1' => array('name' => 'FastIron FGS624P', 'object' => 'snFGS624P'),
'.1.3.6.1.4.1.1991.1.3.45.1.1.1.1' => array('name' => 'FGS624P', 'object' => 'snFGS624PSwitch'),
'.1.3.6.1.4.1.1991.1.3.45.1.1.1.2' => array('name' => 'FGS624P', 'object' => 'snFGS624PRouter'),
'.1.3.6.1.4.1.1991.1.3.45.1.2.1' => array('name' => 'FastIron FGS624XGP', 'object' => 'snFGS624XGP'),
'.1.3.6.1.4.1.1991.1.3.45.1.2.1.1' => array('name' => 'FGS624XGP', 'object' => 'snFGS624XGPSwitch'),
'.1.3.6.1.4.1.1991.1.3.45.1.2.1.2' => array('name' => 'FGS624XGP', 'object' => 'snFGS624XGPRouter'),
'.1.3.6.1.4.1.1991.1.3.45.1.3.1' => array('name' => 'snFGS624PPOE', 'object' => 'snFGS624PPOE'),
'.1.3.6.1.4.1.1991.1.3.45.1.3.1.1' => array('name' => 'snFGS624PPOESwitch', 'object' => 'snFGS624PPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.45.1.3.1.2' => array('name' => 'snFGS624PPOERouter', 'object' => 'snFGS624PPOERouter'),
'.1.3.6.1.4.1.1991.1.3.45.1.4.1' => array('name' => 'snFGS624XGPPOE', 'object' => 'snFGS624XGPPOE'),
'.1.3.6.1.4.1.1991.1.3.45.1.4.1.1' => array('name' => 'snFGS624XGPPOESwitch', 'object' => 'snFGS624XGPPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.45.1.4.1.2' => array('name' => 'snFGS624XGPPOERouter', 'object' => 'snFGS624XGPPOERouter'),
'.1.3.6.1.4.1.1991.1.3.45.2.1.1' => array('name' => 'FastIron GS FGS648P', 'object' => 'snFGS648P'),
'.1.3.6.1.4.1.1991.1.3.45.2.1.1.1' => array('name' => 'FastIron FGS648P', 'object' => 'snFGS648PSwitch'),
'.1.3.6.1.4.1.1991.1.3.45.2.1.1.2' => array('name' => 'FastIron FGS648P', 'object' => 'snFGS648PRouter'),
'.1.3.6.1.4.1.1991.1.3.45.2.2.1' => array('name' => 'snFGS648PPOE', 'object' => 'snFGS648PPOE'),
'.1.3.6.1.4.1.1991.1.3.45.2.2.1.1' => array('name' => 'snFGS648PPOESwitch', 'object' => 'snFGS648PPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.45.2.2.1.2' => array('name' => 'snFGS648PPOERouter', 'object' => 'snFGS648PPOERouter'),
'.1.3.6.1.4.1.1991.1.3.46.1.1.1' => array('name' => 'FastIron FLS624', 'object' => 'snFLS624'),
'.1.3.6.1.4.1.1991.1.3.46.1.1.1.1' => array('name' => 'FastIron FLS624', 'object' => 'snFLS624Switch'),
'.1.3.6.1.4.1.1991.1.3.46.1.1.1.2' => array('name' => 'FastIron FLS624', 'object' => 'snFLS624Router'),
'.1.3.6.1.4.1.1991.1.3.46.2.1.1' => array('name' => 'FastIron FLS648', 'object' => 'snFLS648'),
'.1.3.6.1.4.1.1991.1.3.46.2.1.1.1' => array('name' => 'FastIron FLS648', 'object' => 'snFLS648Switch'),
'.1.3.6.1.4.1.1991.1.3.46.2.1.1.2' => array('name' => 'FastIron FLS648', 'object' => 'snFLS648Router'),
'.1.3.6.1.4.1.1991.1.3.47.1' => array('name' => 'ServerIron SI100', 'object' => 'snSI100'),
'.1.3.6.1.4.1.1991.1.3.47.1.1' => array('name' => 'ServerIron SI100', 'object' => 'snSI100Switch'),
'.1.3.6.1.4.1.1991.1.3.47.1.2' => array('name' => 'ServerIron SI100', 'object' => 'snSI100Router'),
'.1.3.6.1.4.1.1991.1.3.47.10' => array('name' => 'ServerIronGT E Plus series', 'object' => 'snServerIronGTePlus'),
'.1.3.6.1.4.1.1991.1.3.47.10.1' => array('name' => 'ServerIronGT E Plus', 'object' => 'snServerIronGTePlusSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.10.2' => array('name' => 'ServerIronGT E Plus', 'object' => 'snServerIronGTePlusRouter'),
'.1.3.6.1.4.1.1991.1.3.47.11' => array('name' => 'ServerIron4G series', 'object' => 'snServerIron4G'),
'.1.3.6.1.4.1.1991.1.3.47.11.1' => array('name' => 'ServerIron4G', 'object' => 'snServerIron4GSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.11.2' => array('name' => 'ServerIron4G', 'object' => 'snServerIron4GRouter'),
'.1.3.6.1.4.1.1991.1.3.47.12' => array('name' => 'serverIronAdx1000', 'object' => 'serverIronAdx1000'),
'.1.3.6.1.4.1.1991.1.3.47.12.1' => array('name' => 'serverIronAdx1000Switch', 'object' => 'serverIronAdx1000Switch'),
'.1.3.6.1.4.1.1991.1.3.47.12.2' => array('name' => 'serverIronAdx1000Router', 'object' => 'serverIronAdx1000Router'),
'.1.3.6.1.4.1.1991.1.3.47.13' => array('name' => 'serverIronAdx1000Ssl', 'object' => 'serverIronAdx1000Ssl'),
'.1.3.6.1.4.1.1991.1.3.47.13.1' => array('name' => 'serverIronAdx1000SslSwitch', 'object' => 'serverIronAdx1000SslSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.13.2' => array('name' => 'serverIronAdx1000SslRouter', 'object' => 'serverIronAdx1000SslRouter'),
'.1.3.6.1.4.1.1991.1.3.47.14' => array('name' => 'serverIronAdx4000', 'object' => 'serverIronAdx4000'),
'.1.3.6.1.4.1.1991.1.3.47.14.1' => array('name' => 'serverIronAdx4000Switch', 'object' => 'serverIronAdx4000Switch'),
'.1.3.6.1.4.1.1991.1.3.47.14.2' => array('name' => 'serverIronAdx4000Router', 'object' => 'serverIronAdx4000Router'),
'.1.3.6.1.4.1.1991.1.3.47.15' => array('name' => 'serverIronAdx4000Ssl', 'object' => 'serverIronAdx4000Ssl'),
'.1.3.6.1.4.1.1991.1.3.47.15.1' => array('name' => 'serverIronAdx4000SslSwitch', 'object' => 'serverIronAdx4000SslSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.15.2' => array('name' => 'serverIronAdx4000SslRouter', 'object' => 'serverIronAdx4000SslRouter'),
'.1.3.6.1.4.1.1991.1.3.47.16' => array('name' => 'serverIronAdx8000', 'object' => 'serverIronAdx8000'),
'.1.3.6.1.4.1.1991.1.3.47.16.1' => array('name' => 'serverIronAdx8000Switch', 'object' => 'serverIronAdx8000Switch'),
'.1.3.6.1.4.1.1991.1.3.47.16.2' => array('name' => 'serverIronAdx8000Router', 'object' => 'serverIronAdx8000Router'),
'.1.3.6.1.4.1.1991.1.3.47.17' => array('name' => 'serverIronAdx8000Ssl', 'object' => 'serverIronAdx8000Ssl'),
'.1.3.6.1.4.1.1991.1.3.47.17.1' => array('name' => 'serverIronAdx8000SslSwitch', 'object' => 'serverIronAdx8000SslSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.17.2' => array('name' => 'serverIronAdx8000SslRouter', 'object' => 'serverIronAdx8000SslRouter'),
'.1.3.6.1.4.1.1991.1.3.47.18' => array('name' => 'serverIronAdx10000', 'object' => 'serverIronAdx10000'),
'.1.3.6.1.4.1.1991.1.3.47.18.1' => array('name' => 'serverIronAdx10000Switch', 'object' => 'serverIronAdx10000Switch'),
'.1.3.6.1.4.1.1991.1.3.47.18.2' => array('name' => 'serverIronAdx10000Router', 'object' => 'serverIronAdx10000Router'),
'.1.3.6.1.4.1.1991.1.3.47.19' => array('name' => 'serverIronAdx10000Ssl', 'object' => 'serverIronAdx10000Ssl'),
'.1.3.6.1.4.1.1991.1.3.47.19.1' => array('name' => 'serverIronAdx10000SslSwitch', 'object' => 'serverIronAdx10000SslSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.19.2' => array('name' => 'serverIronAdx10000SslRouter', 'object' => 'serverIronAdx10000SslRouter'),
'.1.3.6.1.4.1.1991.1.3.47.2' => array('name' => 'ServerIron 350 series', 'object' => 'snSI350'),
'.1.3.6.1.4.1.1991.1.3.47.2.1' => array('name' => 'SI350', 'object' => 'snSI350Switch'),
'.1.3.6.1.4.1.1991.1.3.47.2.2' => array('name' => 'SI350', 'object' => 'snSI350Router'),
'.1.3.6.1.4.1.1991.1.3.47.3' => array('name' => 'ServerIron 450 series', 'object' => 'snSI450'),
'.1.3.6.1.4.1.1991.1.3.47.3.1' => array('name' => 'SI450', 'object' => 'snSI450Switch'),
'.1.3.6.1.4.1.1991.1.3.47.3.2' => array('name' => 'SI450', 'object' => 'snSI450Router'),
'.1.3.6.1.4.1.1991.1.3.47.4' => array('name' => 'ServerIron 850 series', 'object' => 'snSI850'),
'.1.3.6.1.4.1.1991.1.3.47.4.1' => array('name' => 'SI850', 'object' => 'snSI850Switch'),
'.1.3.6.1.4.1.1991.1.3.47.4.2' => array('name' => 'SI850', 'object' => 'snSI850Router'),
'.1.3.6.1.4.1.1991.1.3.47.5' => array('name' => 'ServerIron 350 Plus series', 'object' => 'snSI350Plus'),
'.1.3.6.1.4.1.1991.1.3.47.5.1' => array('name' => 'SI350 Plus', 'object' => 'snSI350PlusSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.5.2' => array('name' => 'SI350 Plus', 'object' => 'snSI350PlusRouter'),
'.1.3.6.1.4.1.1991.1.3.47.6' => array('name' => 'ServerIron 450 Plus series', 'object' => 'snSI450Plus'),
'.1.3.6.1.4.1.1991.1.3.47.6.1' => array('name' => 'SI450 Plus', 'object' => 'snSI450PlusSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.6.2' => array('name' => 'SI450 Plus', 'object' => 'snSI450PlusRouter'),
'.1.3.6.1.4.1.1991.1.3.47.7' => array('name' => 'ServerIron 850 Plus series', 'object' => 'snSI850Plus'),
'.1.3.6.1.4.1.1991.1.3.47.7.1' => array('name' => 'SI850 Plus', 'object' => 'snSI850PlusSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.7.2' => array('name' => 'SI850 Plus', 'object' => 'snSI850PlusRouter'),
'.1.3.6.1.4.1.1991.1.3.47.8' => array('name' => 'ServerIronGT C series', 'object' => 'snServerIronGTc'),
'.1.3.6.1.4.1.1991.1.3.47.8.1' => array('name' => 'ServerIronGT C', 'object' => 'snServerIronGTcSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.8.2' => array('name' => 'ServerIronGT C', 'object' => 'snServerIronGTcRouter'),
'.1.3.6.1.4.1.1991.1.3.47.9' => array('name' => 'ServerIronGT E series', 'object' => 'snServerIronGTe'),
'.1.3.6.1.4.1.1991.1.3.47.9.1' => array('name' => 'ServerIronGT E', 'object' => 'snServerIronGTeSwitch'),
'.1.3.6.1.4.1.1991.1.3.47.9.2' => array('name' => 'ServerIronGT E', 'object' => 'snServerIronGTeRouter'),
'.1.3.6.1.4.1.1991.1.3.48.1' => array('name' => 'snFastIronStack', 'object' => 'snFastIronStack'),
'.1.3.6.1.4.1.1991.1.3.48.1.1' => array('name' => 'snFastIronStackSwitch', 'object' => 'snFastIronStackSwitch'),
'.1.3.6.1.4.1.1991.1.3.48.1.2' => array('name' => 'snFastIronStackRouter', 'object' => 'snFastIronStackRouter'),
'.1.3.6.1.4.1.1991.1.3.48.2' => array('name' => 'snFastIronStackFCX', 'object' => 'snFastIronStackFCX'),
'.1.3.6.1.4.1.1991.1.3.48.2.1' => array('name' => 'FCX switch', 'object' => 'snFastIronStackFCXSwitch'),
'.1.3.6.1.4.1.1991.1.3.48.2.2' => array('name' => 'FCX Base L3 router', 'object' => 'snFastIronStackFCXBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.48.2.3' => array('name' => 'FCX Premium Router', 'object' => 'snFastIronStackFCXRouter'),
'.1.3.6.1.4.1.1991.1.3.48.2.4' => array('name' => 'FCX Advanced Premium Router (BGP)', 'object' => 'snFastIronStackFCXAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.49.1' => array('name' => 'NetIron CES 2024F', 'object' => 'snCes2024F'),
'.1.3.6.1.4.1.1991.1.3.49.2' => array('name' => 'NetIron CES 2024C', 'object' => 'snCes2024C'),
'.1.3.6.1.4.1.1991.1.3.49.3' => array('name' => 'NetIron CES 2048F', 'object' => 'snCes2048F'),
'.1.3.6.1.4.1.1991.1.3.49.4' => array('name' => 'NetIron CES 2048C', 'object' => 'snCes2048C'),
'.1.3.6.1.4.1.1991.1.3.49.5' => array('name' => 'NetIron CES 2048F + 2x10G', 'object' => 'snCes2048FX'),
'.1.3.6.1.4.1.1991.1.3.49.6' => array('name' => 'NetIron CES 2048C + 2x10G', 'object' => 'snCes2048CX'),
'.1.3.6.1.4.1.1991.1.3.5' => array('name' => 'snTurboIron8', 'object' => 'snTurboIron8'),
'.1.3.6.1.4.1.1991.1.3.5.1' => array('name' => 'Stackable TurboIron 8', 'object' => 'snT8Switch'),
'.1.3.6.1.4.1.1991.1.3.5.2' => array('name' => 'Stackable TurboIron 8', 'object' => 'snT8Router'),
'.1.3.6.1.4.1.1991.1.3.5.3' => array('name' => 'snT8SI', 'object' => 'snT8SI'),
'.1.3.6.1.4.1.1991.1.3.5.4' => array('name' => 'Stackable ServerIronXLG', 'object' => 'snT8SIXLG'),
'.1.3.6.1.4.1.1991.1.3.50.1.1.1' => array('name' => 'snFLSLC624', 'object' => 'snFLSLC624'),
'.1.3.6.1.4.1.1991.1.3.50.1.1.1.1' => array('name' => 'snFLSLC624Switch', 'object' => 'snFLSLC624Switch'),
'.1.3.6.1.4.1.1991.1.3.50.1.1.1.2' => array('name' => 'snFLSLC624Router', 'object' => 'snFLSLC624Router'),
'.1.3.6.1.4.1.1991.1.3.50.1.2.1' => array('name' => 'snFLSLC624POE', 'object' => 'snFLSLC624POE'),
'.1.3.6.1.4.1.1991.1.3.50.1.2.1.1' => array('name' => 'snFLSLC624POESwitch', 'object' => 'snFLSLC624POESwitch'),
'.1.3.6.1.4.1.1991.1.3.50.1.2.1.2' => array('name' => 'snFLSLC624POERouter', 'object' => 'snFLSLC624POERouter'),
'.1.3.6.1.4.1.1991.1.3.50.2.1.1' => array('name' => 'snFLSLC648', 'object' => 'snFLSLC648'),
'.1.3.6.1.4.1.1991.1.3.50.2.1.1.1' => array('name' => 'snFLSLC648Switch', 'object' => 'snFLSLC648Switch'),
'.1.3.6.1.4.1.1991.1.3.50.2.1.1.2' => array('name' => 'snFLSLC648Router', 'object' => 'snFLSLC648Router'),
'.1.3.6.1.4.1.1991.1.3.50.2.2.1' => array('name' => 'snFLSLC648POE', 'object' => 'snFLSLC648POE'),
'.1.3.6.1.4.1.1991.1.3.50.2.2.1.1' => array('name' => 'snFLSLC648POESwitch', 'object' => 'snFLSLC648POESwitch'),
'.1.3.6.1.4.1.1991.1.3.50.2.2.1.2' => array('name' => 'snFLSLC648POERouter', 'object' => 'snFLSLC648POERouter'),
'.1.3.6.1.4.1.1991.1.3.51.1' => array('name' => 'NetIron CER 2024F', 'object' => 'snCer2024F'),
'.1.3.6.1.4.1.1991.1.3.51.2' => array('name' => 'NetIron CER 2024C', 'object' => 'snCer2024C'),
'.1.3.6.1.4.1.1991.1.3.51.3' => array('name' => 'NetIron CER 2048F', 'object' => 'snCer2048F'),
'.1.3.6.1.4.1.1991.1.3.51.4' => array('name' => 'NetIron CER 2048C', 'object' => 'snCer2048C'),
'.1.3.6.1.4.1.1991.1.3.51.5' => array('name' => 'NetIron CER 2048F + 2x10G', 'object' => 'snCer2048FX'),
'.1.3.6.1.4.1.1991.1.3.51.6' => array('name' => 'NetIron CER 2048C + 2x10G', 'object' => 'snCer2048CX'),
'.1.3.6.1.4.1.1991.1.3.52.1.1.1' => array('name' => 'FastIron WS Switch(FWS) 24-port 10/100', 'object' => 'snFWS624'),
'.1.3.6.1.4.1.1991.1.3.52.1.1.1.1' => array('name' => 'FWS624 switch', 'object' => 'snFWS624Switch'),
'.1.3.6.1.4.1.1991.1.3.52.1.1.1.2' => array('name' => 'FWS624 Base L3 router', 'object' => 'snFWS624BaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.1.1.1.3' => array('name' => 'FWS624 Edge Prem router', 'object' => 'snFWS624EdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.1.2.1' => array('name' => 'FastIron WS Switch(FWS) 24-port 10/100/1000', 'object' => 'snFWS624G'),
'.1.3.6.1.4.1.1991.1.3.52.1.2.1.1' => array('name' => 'FWS624G switch', 'object' => 'snFWS624GSwitch'),
'.1.3.6.1.4.1.1991.1.3.52.1.2.1.2' => array('name' => 'FWS624G Base L3 router', 'object' => 'snFWS624GBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.1.2.1.3' => array('name' => 'FWS624G Edge Prem router', 'object' => 'snFWS624GEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.1.3.1' => array('name' => 'FastIron WS Switch(FWS) 24-port 10/100 POE', 'object' => 'snFWS624POE'),
'.1.3.6.1.4.1.1991.1.3.52.1.3.1.1' => array('name' => 'FWS624-POE switch', 'object' => 'snFWS624POESwitch'),
'.1.3.6.1.4.1.1991.1.3.52.1.3.1.2' => array('name' => 'FWS624-POE Base L3 router', 'object' => 'snFWS624POEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.1.3.1.3' => array('name' => 'FWS624-POE Edge Prem router', 'object' => 'snFWS624POEEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.1.4.1' => array('name' => 'FastIron WS Switch(FWS) 24-port 10/100/1000 POE', 'object' => 'snFWS624GPOE'),
'.1.3.6.1.4.1.1991.1.3.52.1.4.1.1' => array('name' => 'FWS624G-POE switch', 'object' => 'snFWS624GPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.52.1.4.1.2' => array('name' => 'FWS624G-POE Base L3 router', 'object' => 'snFWS624GPOEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.1.4.1.3' => array('name' => 'FWS624G-POE Edge Prem router', 'object' => 'snFWS624GPOEEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.2.1.1' => array('name' => 'FastIron WS Switch(FWS) 48-port 10/100 POE Ready', 'object' => 'snFWS648'),
'.1.3.6.1.4.1.1991.1.3.52.2.1.1.1' => array('name' => 'FWS648 switch', 'object' => 'snFWS648Switch'),
'.1.3.6.1.4.1.1991.1.3.52.2.1.1.2' => array('name' => 'FWS648 Base L3 router', 'object' => 'snFWS648BaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.2.1.1.3' => array('name' => 'FWS648 Edge Prem router', 'object' => 'snFWS648EdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.2.2.1' => array('name' => 'FastIron WS Switch(FWS) 48-port 10/100/1000 POE Ready', 'object' => 'snFWS648G'),
'.1.3.6.1.4.1.1991.1.3.52.2.2.1.1' => array('name' => 'FWS648G switch', 'object' => 'snFWS648GSwitch'),
'.1.3.6.1.4.1.1991.1.3.52.2.2.1.2' => array('name' => 'FWS648G Base L3 router', 'object' => 'snFWS648GBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.2.2.1.3' => array('name' => 'FWS648G Edge Prem router', 'object' => 'snFWS648GEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.2.3.1' => array('name' => 'FastIron WS Switch(FWS) 48-port 10/100 POE', 'object' => 'snFWS648POE'),
'.1.3.6.1.4.1.1991.1.3.52.2.3.1.1' => array('name' => 'FWS648-POE switch', 'object' => 'snFWS648POESwitch'),
'.1.3.6.1.4.1.1991.1.3.52.2.3.1.2' => array('name' => 'FWS648-POE Base L3 router', 'object' => 'snFWS648POEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.2.3.1.3' => array('name' => 'FWS648-POE Edge Prem router', 'object' => 'snFWS648POEEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.52.2.4.1' => array('name' => 'FastIron WS Switch(FWS) 48-port 10/100/1000 POE', 'object' => 'snFWS648GPOE'),
'.1.3.6.1.4.1.1991.1.3.52.2.4.1.1' => array('name' => 'FWS648G-POE switch', 'object' => 'snFWS648GPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.52.2.4.1.2' => array('name' => 'FWS648G-POE Base L3 router', 'object' => 'snFWS648GPOEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.52.2.4.1.3' => array('name' => 'FWS648G-POE Edge Prem router', 'object' => 'snFWS648GPOEEdgePremRouter'),
'.1.3.6.1.4.1.1991.1.3.53' => array('name' => 'snTurboIron2', 'object' => 'snTurboIron2'),
'.1.3.6.1.4.1.1991.1.3.53.1.1' => array('name' => 'TurboIron 24X switch', 'object' => 'snTI2X24Switch'),
'.1.3.6.1.4.1.1991.1.3.53.1.2' => array('name' => 'TurboIron 24X router', 'object' => 'snTI2X24Router'),
'.1.3.6.1.4.1.1991.1.3.53.2.1' => array('name' => 'TurboIron 48X switch', 'object' => 'snTI2X48Switch'),
'.1.3.6.1.4.1.1991.1.3.53.2.2' => array('name' => 'TurboIron 48X router', 'object' => 'snTI2X48Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.1.1' => array('name' => 'snFCX624S', 'object' => 'snFCX624S'),
'.1.3.6.1.4.1.1991.1.3.54.1.1.1.1' => array('name' => 'snFCX624SSwitch', 'object' => 'snFCX624SSwitch'),
'.1.3.6.1.4.1.1991.1.3.54.1.1.1.2' => array('name' => 'snFCX624SBaseL3Router', 'object' => 'snFCX624SBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.1.1.3' => array('name' => 'FCX624S Premium Router', 'object' => 'snFCX624SRouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.1.1.4' => array('name' => 'snFCX624SAdvRouter', 'object' => 'snFCX624SAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.2.1' => array('name' => 'snFCX624SHPOE', 'object' => 'snFCX624SHPOE'),
'.1.3.6.1.4.1.1991.1.3.54.1.2.1.1' => array('name' => 'snFCX624SHPOESwitch', 'object' => 'snFCX624SHPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.54.1.2.1.2' => array('name' => 'snFCX624SHPOEBaseL3Router', 'object' => 'snFCX624SHPOEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.2.1.3' => array('name' => 'snFCX624SHPOERouter', 'object' => 'snFCX624SHPOERouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.2.1.4' => array('name' => 'snFCX624SHPOEAdvRouter', 'object' => 'snFCX624SHPOEAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.3.1' => array('name' => 'snFCX624SF', 'object' => 'snFCX624SF'),
'.1.3.6.1.4.1.1991.1.3.54.1.3.1.1' => array('name' => 'snFCX624SFSwitch', 'object' => 'snFCX624SFSwitch'),
'.1.3.6.1.4.1.1991.1.3.54.1.3.1.2' => array('name' => 'snFCX624SFBaseL3Router', 'object' => 'snFCX624SFBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.3.1.3' => array('name' => 'snFCX624SFRouter', 'object' => 'snFCX624SFRouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.3.1.4' => array('name' => 'snFCX624SFAdvRouter', 'object' => 'snFCX624SFAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.1.4.1' => array('name' => 'snFCX624', 'object' => 'snFCX624'),
'.1.3.6.1.4.1.1991.1.3.54.1.4.1.1' => array('name' => 'snFCX624Switch', 'object' => 'snFCX624Switch'),
'.1.3.6.1.4.1.1991.1.3.54.1.4.1.2' => array('name' => 'snFCX624BaseL3Router', 'object' => 'snFCX624BaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.4.1.3' => array('name' => 'snFCX624Router', 'object' => 'snFCX624Router'),
'.1.3.6.1.4.1.1991.1.3.54.1.4.1.4' => array('name' => 'snFCX624AdvRouter', 'object' => 'snFCX624AdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.2.1.1' => array('name' => 'snFCX648S', 'object' => 'snFCX648S'),
'.1.3.6.1.4.1.1991.1.3.54.2.1.1.1' => array('name' => 'FCX648S switch', 'object' => 'snFCX648SSwitch'),
'.1.3.6.1.4.1.1991.1.3.54.2.1.1.2' => array('name' => 'FCX648S Base L3 router', 'object' => 'snFCX648SBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.2.1.1.3' => array('name' => 'FCX648S Premium Router', 'object' => 'snFCX648SRouter'),
'.1.3.6.1.4.1.1991.1.3.54.2.1.1.4' => array('name' => 'FCX648S Advanced Premium Router (BGP)', 'object' => 'snFCX648SAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.2.2.1' => array('name' => 'FastIron CX Switch(FCX-S) 48-port 10/100/1000', 'object' => 'snFCX648SHPOE'),
'.1.3.6.1.4.1.1991.1.3.54.2.2.1.1' => array('name' => 'FCX648S-HPOE switch', 'object' => 'snFCX648SHPOESwitch'),
'.1.3.6.1.4.1.1991.1.3.54.2.2.1.2' => array('name' => 'FCX648S-HPOE Base L3 router', 'object' => 'snFCX648SHPOEBaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.2.2.1.3' => array('name' => 'FCX648S-HPOE Premium Router', 'object' => 'snFCX648SHPOERouter'),
'.1.3.6.1.4.1.1991.1.3.54.2.2.1.4' => array('name' => 'FCX648S-HPOE Advanced Premium Router (BGP)', 'object' => 'snFCX648SHPOEAdvRouter'),
'.1.3.6.1.4.1.1991.1.3.54.2.4.1' => array('name' => 'FastIron CX Switch(FCX) 48-port 10/100/1000', 'object' => 'snFCX648'),
'.1.3.6.1.4.1.1991.1.3.54.2.4.1.1' => array('name' => 'FCX648 switch', 'object' => 'snFCX648Switch'),
'.1.3.6.1.4.1.1991.1.3.54.2.4.1.2' => array('name' => 'FCX648 Base L3 router', 'object' => 'snFCX648BaseL3Router'),
'.1.3.6.1.4.1.1991.1.3.54.2.4.1.3' => array('name' => 'FCX648 Premium Router', 'object' => 'snFCX648Router'),
'.1.3.6.1.4.1.1991.1.3.54.2.4.1.4' => array('name' => 'FCX648 Advanced Premium Router (BGP)', 'object' => 'snFCX648AdvRouter'),
'.1.3.6.1.4.1.1991.1.3.55.1' => array('name' => 'Brocade MLXe16', 'object' => 'snBrocadeMLXe16'),
'.1.3.6.1.4.1.1991.1.3.55.1.2' => array('name' => 'Brocade MLXe16', 'object' => 'snBrocadeMLXe16Router'),
'.1.3.6.1.4.1.1991.1.3.55.2' => array('name' => 'Brocade MLXe8', 'object' => 'snBrocadeMLXe8'),
'.1.3.6.1.4.1.1991.1.3.55.2.2' => array('name' => 'Brocade MLXe8', 'object' => 'snBrocadeMLXe8Router'),
'.1.3.6.1.4.1.1991.1.3.55.3' => array('name' => 'Brocade MLXe4', 'object' => 'snBrocadeMLXe4'),
'.1.3.6.1.4.1.1991.1.3.55.3.2' => array('name' => 'Brocade MLXe4', 'object' => 'snBrocadeMLXe4Router'),
'.1.3.6.1.4.1.1991.1.3.55.4' => array('name' => 'Brocade MLXe32', 'object' => 'snBrocadeMLXe32'),
'.1.3.6.1.4.1.1991.1.3.55.4.2' => array('name' => 'Brocade MLXe32', 'object' => 'snBrocadeMLXe32Router'),
'.1.3.6.1.4.1.1991.1.3.6' => array('name' => 'snBigIron4000', 'object' => 'snBigIron4000'),
'.1.3.6.1.4.1.1991.1.3.6.1' => array('name' => 'BigIron 4000', 'object' => 'snBI4000Switch'),
'.1.3.6.1.4.1.1991.1.3.6.2' => array('name' => 'BigIron 4000', 'object' => 'snBI4000Router'),
'.1.3.6.1.4.1.1991.1.3.6.3' => array('name' => 'BigServerIron', 'object' => 'snBI4000SI'),
'.1.3.6.1.4.1.1991.1.3.7' => array('name' => 'snBigIron8000', 'object' => 'snBigIron8000'),
'.1.3.6.1.4.1.1991.1.3.7.1' => array('name' => 'BigIron 8000', 'object' => 'snBI8000Switch'),
'.1.3.6.1.4.1.1991.1.3.7.2' => array('name' => 'BigIron 8000', 'object' => 'snBI8000Router'),
'.1.3.6.1.4.1.1991.1.3.7.3' => array('name' => 'BigServerIron', 'object' => 'snBI8000SI'),
'.1.3.6.1.4.1.1991.1.3.8' => array('name' => 'snFastIron2', 'object' => 'snFastIron2'),
'.1.3.6.1.4.1.1991.1.3.8.1' => array('name' => 'FastIron II', 'object' => 'snFI2Switch'),
'.1.3.6.1.4.1.1991.1.3.8.2' => array('name' => 'FastIron II', 'object' => 'snFI2Router'),
'.1.3.6.1.4.1.1991.1.3.9' => array('name' => 'snFastIron2Plus', 'object' => 'snFastIron2Plus'),
'.1.3.6.1.4.1.1991.1.3.9.1' => array('name' => 'FastIron II Plus', 'object' => 'snFI2PlusSwitch'),
'.1.3.6.1.4.1.1991.1.3.9.2' => array('name' => 'FastIron II Plus', 'object' => 'snFI2PlusRouter'),
);

$rewrite_liebert_hardware = array(
  // UpsProducts - Liebert UPS Registrations
  'lgpSeries7200' => array('name' => 'Series 7200 UPS', 'type' => 'ups'),
  'lgpUPStationGXT' => array('name' => 'UPStationGXT UPS', 'type' => 'ups'),
  'lgpPowerSureInteractive' => array('name' => 'PowerSure Interactive UPS', 'type' => 'ups'),
  'lgpNfinity' => array('name' => 'Nfinity UPS', 'type' => 'ups'),
  'lgpNpower' => array('name' => 'Npower UPS', 'type' => 'ups'),
  'lgpGXT2Dual' => array('name' => 'GXT2 Dual Inverter', 'type' => 'ups'),
  'lgpPowerSureInteractive2' => array('name' => 'PowerSure Interactive 2 UPS', 'type' => 'ups'),
  'lgpNX' => array('name' => 'ENPC Nx UPS', 'type' => 'ups'),
  'lgpHiNet' => array('name' => 'Hiross HiNet UPS', 'type' => 'ups'),
  'lgpNXL' => array('name' => 'NXL UPS', 'type' => 'ups'),
  'lgpSuper400' => array('name' => 'Super 400 UPS', 'type' => 'ups'),
  'lgpSeries600or610' => array('name' => 'Series 600/610 UPS', 'type' => 'ups'),
  'lgpSeries300' => array('name' => 'Series 300 UPS', 'type' => 'ups'),
  'lgpSeries610SMS' => array('name' => 'Series 610 Single Module System (SMS) UPS', 'type' => 'ups'),
  'lgpSeries610MMU' => array('name' => 'Series 610 Multi Module Unit (MMU) UPS', 'type' => 'ups'),
  'lgpSeries610SCC' => array('name' => 'Series 610 System Control Cabinet (SCC) UPS', 'type' => 'ups'),
  'lgpNXr' => array('name' => 'APM UPS', 'type' => 'ups'),
  // AcProducts - Liebert Environmental Air Conditioning Registrations
  'lgpAdvancedMicroprocessor' => array('name' => 'Environmental Advanced Microprocessor control', 'type' => 'environment'),
  'lgpStandardMicroprocessor' => array('name' => 'Environmental Standard Microprocessor control', 'type' => 'environment'),
  'lgpMiniMate2' => array('name' => 'Environmental Mini-Mate 2', 'type' => 'environment'),
  'lgpHimod' => array('name' => 'Environmental Himod', 'type' => 'environment'),
  'lgpCEMS100orLECS15' => array('name' => 'Australia Environmental CEMS100 and LECS15 control', 'type' => 'environment'),
  'lgpIcom' => array('name' => 'Environmental iCOM control', 'type' => 'environment'),
  'lgpIcomPA' => array('name' => 'iCOM PA (Floor mount) Environmental', 'type' => 'environment'),
  'lgpIcomXD' => array('name' => 'iCOM XD (Rack cooling with compressor) Environmental', 'type' => 'environment'),
  'lgpIcomXP' => array('name' => 'iCOM XP (Pumped refrigerant) Environmental', 'type' => 'environment'),
  'lgpIcomSC' => array('name' => 'iCOM SC (Chiller) Environmental', 'type' => 'environment'),
  'lgpIcomCR' => array('name' => 'iCOM CR (Computer Row) Environmental', 'type' => 'environment'),
  // iCOM PA Family - Liebert PA (Floor mount) Environmental Registrations
  'lgpIcomPAtypeDS' => array('name' => 'DS Environmental', 'type' => 'environment'),
  'lgpIcomPAtypeHPM' => array('name' => 'HPM Environmental', 'type' => 'environment'),
  'lgpIcomPAtypeChallenger' => array('name' => 'Challenger Environmental', 'type' => 'environment'),
  'lgpIcomPAtypePeX' => array('name' => 'PeX Environmental', 'type' => 'environment'),
  'lgpIcomPAtypeDeluxeSys3' => array('name' => 'Deluxe System 3 Environmental', 'type' => 'environment'),
  'lgpIcomPAtypeJumboCW' => array('name' => 'Jumbo CW Environmental', 'type' => 'environment'),
  'lgpIcomPAtypeDSE' => array('name' => 'DSE Environmental', 'type' => 'environment'),
  'lgpIcomPAtypePEXS' => array('name' => 'PEX-S Environmental', 'type' => 'environment'),
  'lgpIcomPAtypePDX' => array('name' => 'PDX - PCW Environmental', 'type' => 'environment'),
  // iCOM XD Family - Liebert XD Environmental Registrations
  'lgpIcomXDtypeXDF' => array('name' => 'XDF Environmental', 'type' => 'environment'),
  'lgpIcomXDtypeXDFN' => array('name' => 'XDFN Environmental', 'type' => 'environment'),
  'lgpIcomXPtypeXDP' => array('name' => 'XDP Environmental', 'type' => 'environment'),
  'lgpIcomXPtypeXDPCray' => array('name' => 'XDP Environmental products for Cray', 'type' => 'environment'),
  'lgpIcomXPtypeXDC' => array('name' => 'XDC Environmental', 'type' => 'environment'),
  'lgpIcomXPtypeXDPW' => array('name' => 'XDP-W Environmental', 'type' => 'environment'),
  // iCOM SC Family - Liebert SC (Chillers) Environmental Registrations
  'lgpIcomSCtypeHPC' => array('name' => 'HPC Environmental', 'type' => 'environment'),
  'lgpIcomSCtypeHPCSSmall' => array('name' => 'HPC-S Small', 'type' => 'environment'),
  'lgpIcomSCtypeHPCSLarge' => array('name' => 'HPC-S Large', 'type' => 'environment'),
  'lgpIcomSCtypeHPCR' => array('name' => 'HPC-R', 'type' => 'environment'),
  'lgpIcomSCtypeHPCM' => array('name' => 'HPC-M', 'type' => 'environment'),
  'lgpIcomSCtypeHPCL' => array('name' => 'HPC-L', 'type' => 'environment'),
  'lgpIcomSCtypeHPCW' => array('name' => 'HPC-W', 'type' => 'environment'),
  // iCOM CR Family - Liebert CR (Computer Row) Environmental Registrations
  'lgpIcomCRtypeCRV' => array('name' => 'CRV Environmental', 'type' => 'environment'),
  // PowerConditioningProducts - Liebert Power Conditioning Registrations
  'lgpPMP' => array('name' => 'PMP (Power Monitoring Panel)', 'type' => 'power'),
  'lgpEPMP' => array('name' => 'EPMP (Extended Power Monitoring Panel)', 'type' => 'power'),
  // Transfer Switch Products - Liebert Transfer Switch Registrations
  'lgpStaticTransferSwitchEDS' => array('name' => 'EDS Static Transfer Switch', 'type' => 'network'),
  'lgpStaticTransferSwitch1' => array('name' => 'Static Transfer Switch 1', 'type' => 'network'),
  'lgpStaticTransferSwitch2' => array('name' => 'Static Transfer Switch 2', 'type' => 'network'),
  'lgpStaticTransferSwitch2FourPole' => array('name' => 'Static Transfer Switch 2 - 4Pole', 'type' => 'network'),
  // MultiLink Products - Liebert MultiLink Registrations
  'lgpMultiLinkBasicNotification' => array('name' => 'MultiLink MLBN device proxy', 'type' => 'power'),
  // Power Distribution Products - Liebert Power Conditioning Distribution
  'lgpRackPDU' => array('name' => 'Rack Power Distribution Products (RPDU)', 'type' => 'pdu'),
  'lgpMPX' => array('name' => 'MPX product distribution (PDU)', 'type' => 'pdu'),
  'lgpMPH' => array('name' => 'MPH product distribution (PDU)', 'type' => 'pdu'),
  // Combined System Product Registrations
  'lgpPMPandLDMF' => array('name' => 'PMP version 4/LDMF', 'type' => 'power'),
  'lgpPMPgeneric' => array('name' => 'PMP version 4', 'type' => 'power'),
  'lgpPMPonFPC' => array('name' => 'PMP version 4 for FPC', 'type' => 'power'),
  'lgpPMPonPPC' => array('name' => 'PMP version 4 for PPC', 'type' => 'power'),
  'lgpPMPonFDC' => array('name' => 'PMP version 4 for FDC', 'type' => 'power'),
  'lgpPMPonRDC' => array('name' => 'PMP version 4 for RDC', 'type' => 'power'),
  'lgpPMPonEXC' => array('name' => 'PMP version 4 for EXC', 'type' => 'power'),
  'lgpPMPonSTS2' => array('name' => 'PMP version 4 for STS2', 'type' => 'power'),
  'lgpPMPonSTS2PDU' => array('name' => 'PMP version 4 for STS2/PDU', 'type' => 'power'),
);

// rewrite oids used for snmp_translate()
$rewrite_oids = array(
  // JunOS/JunOSe BGP4 V2
  'BGP4-V2-MIB-JUNIPER' => array(
    'jnxBgpM2PeerTable'                 => '.1.3.6.1.4.1.2636.5.1.1.2.1.1',
    'jnxBgpM2PeerState'                 => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.2',
    'jnxBgpM2PeerStatus'                => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.3',
    'jnxBgpM2PeerInUpdates'             => '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.1',
    'jnxBgpM2PeerOutUpdates'            => '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.2',
    'jnxBgpM2PeerInTotalMessages'       => '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.3',
    'jnxBgpM2PeerOutTotalMessages'      => '.1.3.6.1.4.1.2636.5.1.1.2.6.1.1.4',
    'jnxBgpM2PeerFsmEstablishedTime'    => '.1.3.6.1.4.1.2636.5.1.1.2.4.1.1.1',
    'jnxBgpM2PeerInUpdatesElapsedTime'  => '.1.3.6.1.4.1.2636.5.1.1.2.4.1.1.2',
    'jnxBgpM2PeerLocalAddr'             => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.7',
    'jnxBgpM2PeerIdentifier'            => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.1',
    'jnxBgpM2PeerRemoteAs'              => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13',
    'jnxBgpM2PeerRemoteAddr'            => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.11',
    'jnxBgpM2PeerRemoteAddrType'        => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.10',
    'jnxBgpM2PeerIndex'                 => '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.14',
    'jnxBgpM2PrefixInPrefixesAccepted'  => '.1.3.6.1.4.1.2636.5.1.1.2.6.2.1.8',
    'jnxBgpM2PrefixInPrefixesRejected'  => '.1.3.6.1.4.1.2636.5.1.1.2.6.2.1.9',
    'jnxBgpM2PrefixOutPrefixes'         => '.1.3.6.1.4.1.2636.5.1.1.2.6.2.1.10',
    'jnxBgpM2PrefixCountersSafi'        => '.1.3.6.1.4.1.2636.5.1.1.2.6.2.1.2',
    'jnxBgpM2CfgPeerAdminStatus'        => '.1.3.6.1.4.1.2636.5.1.1.2.8.1.1.1',
  ),
  // Force10 BGP4 V2
  'FORCE10-BGP4-V2-MIB' => array(
    'f10BgpM2PeerTable'                 => '.1.3.6.1.4.1.6027.20.1.2.1.1',
    'f10BgpM2PeerState'                 => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.3',
    'f10BgpM2PeerStatus'                => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.4',
    'f10BgpM2PeerInUpdates'             => '.1.3.6.1.4.1.6027.20.1.2.6.1.1.1',
    'f10BgpM2PeerOutUpdates'            => '.1.3.6.1.4.1.6027.20.1.2.6.1.1.2',
    'f10BgpM2PeerInTotalMessages'       => '.1.3.6.1.4.1.6027.20.1.2.6.1.1.3',
    'f10BgpM2PeerOutTotalMessages'      => '.1.3.6.1.4.1.6027.20.1.2.6.1.1.4',
    'f10BgpM2PeerFsmEstablishedTime'    => '.1.3.6.1.4.1.6027.20.1.2.3.1.1.1',
    'f10BgpM2PeerInUpdatesElapsedTime'  => '.1.3.6.1.4.1.6027.20.1.2.3.1.1.2',
    'f10BgpM2PeerLocalAddr'             => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.8',
    'f10BgpM2PeerIdentifier'            => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.2',
    'f10BgpM2PeerRemoteAs'              => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.14',
    'f10BgpM2PeerRemoteAddr'            => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.12',
    'f10BgpM2PeerRemoteAddrType'        => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.11',
    'f10BgpM2PeerIndex'                 => '.1.3.6.1.4.1.6027.20.1.2.1.1.1.15',
    'f10BgpM2PrefixInPrefixesAccepted'  => '.1.3.6.1.4.1.6027.20.1.2.6.2.1.8',
    'f10BgpM2PrefixInPrefixesRejected'  => '.1.3.6.1.4.1.6027.20.1.2.6.2.1.9',
    'f10BgpM2PrefixOutPrefixes'         => '.1.3.6.1.4.1.6027.20.1.2.6.2.1.10',
    'f10BgpM2PrefixCountersSafi'        => '.1.3.6.1.4.1.6027.20.1.2.6.2.1.2',
    'f10BgpM2CfgPeerAdminStatus'        => '.1.3.6.1.4.1.6027.20.1.2.8.1.1.1'
  ),
  // Arista BGP4 V2
  // Note that these rewrites are only here because of the
  // bgp-peer code; the MIB has the right info.
  // To regenerate this array:
  // SMIPATH=/usr/share/snmp/eos-mibs:/usr/share/snmp/mibs smidump -f identifiers ARISTA-BGP4V2-MIB | awk '{print "'"'"'" $2 "'"'"'", "=>", "'"'"'." $4 "'"'"',"}'
  'ARISTA-BGP4V2-MIB' => array(
    'aristaBgp4V2PeerTable' => '.1.3.6.1.4.1.30065.4.1.1.2',
    'aristaBgp4V2PeerEntry' => '.1.3.6.1.4.1.30065.4.1.1.2.1',
    'aristaBgp4V2PeerInstance' => '.1.3.6.1.4.1.30065.4.1.1.2.1.1',
    'aristaBgp4V2PeerLocalAddrType' => '.1.3.6.1.4.1.30065.4.1.1.2.1.2',
    'aristaBgp4V2PeerLocalAddr' => '.1.3.6.1.4.1.30065.4.1.1.2.1.3',
    'aristaBgp4V2PeerRemoteAddrType' => '.1.3.6.1.4.1.30065.4.1.1.2.1.4',
    'aristaBgp4V2PeerRemoteAddr' => '.1.3.6.1.4.1.30065.4.1.1.2.1.5',
    'aristaBgp4V2PeerLocalPort' => '.1.3.6.1.4.1.30065.4.1.1.2.1.6',
    'aristaBgp4V2PeerLocalAs' => '.1.3.6.1.4.1.30065.4.1.1.2.1.7',
    'aristaBgp4V2PeerLocalIdentifier' => '.1.3.6.1.4.1.30065.4.1.1.2.1.8',
    'aristaBgp4V2PeerRemotePort' => '.1.3.6.1.4.1.30065.4.1.1.2.1.9',
    'aristaBgp4V2PeerRemoteAs' => '.1.3.6.1.4.1.30065.4.1.1.2.1.10',
    'aristaBgp4V2PeerRemoteIdentifier' => '.1.3.6.1.4.1.30065.4.1.1.2.1.11',
    'aristaBgp4V2PeerAdminStatus' => '.1.3.6.1.4.1.30065.4.1.1.2.1.12',
    'aristaBgp4V2PeerState' => '.1.3.6.1.4.1.30065.4.1.1.2.1.13',
    'aristaBgp4V2PeerDescription' => '.1.3.6.1.4.1.30065.4.1.1.2.1.14',
    'aristaBgp4V2PeerErrorsTable' => '.1.3.6.1.4.1.30065.4.1.1.3',
    'aristaBgp4V2PeerErrorsEntry' => '.1.3.6.1.4.1.30065.4.1.1.3.1',
    'aristaBgp4V2PeerLastErrorCodeReceived' => '.1.3.6.1.4.1.30065.4.1.1.3.1.1',
    'aristaBgp4V2PeerLastErrorSubCodeReceived' => '.1.3.6.1.4.1.30065.4.1.1.3.1.2',
    'aristaBgp4V2PeerLastErrorReceivedTime' => '.1.3.6.1.4.1.30065.4.1.1.3.1.3',
    'aristaBgp4V2PeerLastErrorReceivedText' => '.1.3.6.1.4.1.30065.4.1.1.3.1.4',
    'aristaBgp4V2PeerLastErrorReceivedData' => '.1.3.6.1.4.1.30065.4.1.1.3.1.5',
    'aristaBgp4V2PeerLastErrorCodeSent' => '.1.3.6.1.4.1.30065.4.1.1.3.1.6',
    'aristaBgp4V2PeerLastErrorSubCodeSent' => '.1.3.6.1.4.1.30065.4.1.1.3.1.7',
    'aristaBgp4V2PeerLastErrorSentTime' => '.1.3.6.1.4.1.30065.4.1.1.3.1.8',
    'aristaBgp4V2PeerLastErrorSentText' => '.1.3.6.1.4.1.30065.4.1.1.3.1.9',
    'aristaBgp4V2PeerLastErrorSentData' => '.1.3.6.1.4.1.30065.4.1.1.3.1.10',
    'aristaBgp4V2PeerEventTimesTable' => '.1.3.6.1.4.1.30065.4.1.1.4',
    'aristaBgp4V2PeerEventTimesEntry' => '.1.3.6.1.4.1.30065.4.1.1.4.1',
    'aristaBgp4V2PeerFsmEstablishedTime' => '.1.3.6.1.4.1.30065.4.1.1.4.1.1',
    'aristaBgp4V2PeerInUpdatesElapsedTime' => '.1.3.6.1.4.1.30065.4.1.1.4.1.2',
    'aristaBgp4V2PeerConfiguredTimersTable' => '.1.3.6.1.4.1.30065.4.1.1.5',
    'aristaBgp4V2PeerConfiguredTimersEntry' => '.1.3.6.1.4.1.30065.4.1.1.5.1',
    'aristaBgp4V2PeerConnectRetryInterval' => '.1.3.6.1.4.1.30065.4.1.1.5.1.1',
    'aristaBgp4V2PeerHoldTimeConfigured' => '.1.3.6.1.4.1.30065.4.1.1.5.1.2',
    'aristaBgp4V2PeerKeepAliveConfigured' => '.1.3.6.1.4.1.30065.4.1.1.5.1.3',
    'aristaBgp4V2PeerMinASOrigInterval' => '.1.3.6.1.4.1.30065.4.1.1.5.1.4',
    'aristaBgp4V2PeerMinRouteAdverInterval' => '.1.3.6.1.4.1.30065.4.1.1.5.1.5',
    'aristaBgp4V2PeerNegotiatedTimersTable' => '.1.3.6.1.4.1.30065.4.1.1.6',
    'aristaBgp4V2PeerNegotiatedTimersEntry' => '.1.3.6.1.4.1.30065.4.1.1.6.1',
    'aristaBgp4V2PeerHoldTime' => '.1.3.6.1.4.1.30065.4.1.1.6.1.1',
    'aristaBgp4V2PeerKeepAlive' => '.1.3.6.1.4.1.30065.4.1.1.6.1.2',
    'aristaBgp4V2PeerCountersTable' => '.1.3.6.1.4.1.30065.4.1.1.7',
    'aristaBgp4V2PeerCountersEntry' => '.1.3.6.1.4.1.30065.4.1.1.7.1',
    'aristaBgp4V2PeerInUpdates' => '.1.3.6.1.4.1.30065.4.1.1.7.1.1',
    'aristaBgp4V2PeerOutUpdates' => '.1.3.6.1.4.1.30065.4.1.1.7.1.2',
    'aristaBgp4V2PeerInTotalMessages' => '.1.3.6.1.4.1.30065.4.1.1.7.1.3',
    'aristaBgp4V2PeerOutTotalMessages' => '.1.3.6.1.4.1.30065.4.1.1.7.1.4',
    'aristaBgp4V2PeerFsmEstablishedTransitions' => '.1.3.6.1.4.1.30065.4.1.1.7.1.5',
    'aristaBgp4V2PrefixGaugesTable' => '.1.3.6.1.4.1.30065.4.1.1.8',
    'aristaBgp4V2PrefixGaugesEntry' => '.1.3.6.1.4.1.30065.4.1.1.8.1',
    'aristaBgp4V2PrefixGaugesAfi' => '.1.3.6.1.4.1.30065.4.1.1.8.1.1',
    'aristaBgp4V2PrefixGaugesSafi' => '.1.3.6.1.4.1.30065.4.1.1.8.1.2',
    'aristaBgp4V2PrefixInPrefixes' => '.1.3.6.1.4.1.30065.4.1.1.8.1.3',
    'aristaBgp4V2PrefixInPrefixesAccepted' => '.1.3.6.1.4.1.30065.4.1.1.8.1.4',
    'aristaBgp4V2PrefixOutPrefixes' => '.1.3.6.1.4.1.30065.4.1.1.8.1.5',
  ),
  // IPV6-MIB
  'IPV6-MIB' => array(
    'ipv6AddrPfxLength'                 => '.1.3.6.1.2.1.55.1.8.1.2',
    'ipv6AddrType'                      => '.1.3.6.1.2.1.55.1.8.1.3'
  )
);

$rewrite_iftype = array(
  'other' => 'Other',
  'regular1822',
  'hdh1822',
  'ddnX25',
  'rfc877x25',
  'ethernetCsmacd' => 'Ethernet',
  'iso88023Csmacd' => 'Ethernet',
  'iso88024TokenBus',
  'iso88025TokenRing' => 'Token Ring',
  'iso88026Man',
  'starLan' => 'StarLAN',
  'proteon10Mbit',
  'proteon80Mbit',
  'hyperchannel',
  'fddi' => 'FDDI',
  'lapb',
  'sdlc',
  'ds1' => 'DS1',
  'e1' => 'E1',
  'basicISDN' => 'Basic Rate ISDN',
  'primaryISDN' => 'Primary Rate ISDN',
  'propPointToPointSerial' => 'PtP Serial',
  'ppp' => 'PPP',
  'softwareLoopback' => 'Loopback',
  'eon' => 'CLNP over IP',
  'ethernet3Mbit' => 'Ethernet',
  'nsip' => 'XNS over IP',
  'slip' => 'SLIP',
  'ultra' => 'ULTRA technologies',
  'ds3' => 'DS3',
  'sip' => 'SMDS',
  'frameRelay' => 'Frame Relay',
  'rs232' => 'RS232 Serial',
  'para' => 'Parallel',
  'arcnet' => 'Arcnet',
  'arcnetPlus' => 'Arcnet Plus',
  'atm' => 'ATM Cells',
  'miox25',
  'sonet' => 'SONET or SDH',
  'x25ple',
  'iso88022llc',
  'localTalk',
  'smdsDxi',
  'frameRelayService' => 'FRNETSERV-MIB',
  'v35',
  'hssi',
  'hippi',
  'modem' => 'Generic Modem',
  'aal5' => 'AAL5 over ATM',
  'sonetPath' => 'SONET Path',
  'sonetVT' => 'SONET VT',
  'smdsIcip' => 'SMDS InterCarrier Interface',
  'propVirtual' => 'Virtual/Internal',
  'propMultiplexor' => 'proprietary multiplexing',
  'ieee80212' => '100BaseVG',
  'fibreChannel' => 'Fibre Channel',
  'hippiInterface' => 'HIPPI',
  'frameRelayInterconnect' => 'Frame Relay',
  'aflane8023' => 'ATM Emulated LAN for 802.3',
  'aflane8025' => 'ATM Emulated LAN for 802.5',
  'cctEmul' => 'ATM Emulated circuit ',
  'fastEther' => 'Ethernet',
  'isdn' => 'ISDN and X.25',
  'v11' => 'CCITT V.11/X.21',
  'v36' => 'CCITT V.36 ',
  'g703at64k' => 'CCITT G703 at 64Kbps',
  'g703at2mb' => 'Obsolete see DS1-MIB',
  'qllc' => 'SNA QLLC',
  'fastEtherFX' => 'Ethernet',
  'channel' => 'Channel',
  'ieee80211' => 'IEEE802.11 Radio',
  'ibm370parChan' => 'IBM System 360/370 OEMI Channel',
  'escon' => 'IBM Enterprise Systems Connection',
  'dlsw' => 'Data Link Switching',
  'isdns' => 'ISDN S/T',
  'isdnu' => 'ISDN U',
  'lapd' => 'Link Access Protocol D',
  'ipSwitch' => 'IP Switching Objects',
  'rsrb' => 'Remote Source Route Bridging',
  'atmLogical' => 'ATM Logical Port',
  'ds0' => 'Digital Signal Level 0',
  'ds0Bundle' => 'Group of DS0s on the same DS1',
  'bsc' => 'Bisynchronous Protocol',
  'async' => 'Asynchronous Protocol',
  'cnr' => 'Combat Net Radio',
  'iso88025Dtr' => 'ISO 802.5r DTR',
  'eplrs' => 'Ext Pos Loc Report Sys',
  'arap' => 'Appletalk Remote Access Protocol',
  'propCnls' => 'Proprietary Connectionless Protocol',
  'hostPad' => 'CCITT-ITU X.29 PAD Protocol',
  'termPad' => 'CCITT-ITU X.3 PAD Facility',
  'frameRelayMPI' => 'Multiproto Interconnect over FR',
  'x213' => 'CCITT-ITU X213',
  'adsl' => 'ADSL',
  'radsl' => 'Rate-Adapt. DSL',
  'sdsl' => 'SDSL',
  'vdsl' => 'VDSL',
  'iso88025CRFPInt' => 'ISO 802.5 CRFP',
  'myrinet' => 'Myricom Myrinet',
  'voiceEM' => 'Voice recEive and transMit',
  'voiceFXO' => 'Voice FXO',
  'voiceFXS' => 'Voice FXS',
  'voiceEncap' => 'Voice Encapsulation',
  'voiceOverIp' => 'Voice over IP',
  'atmDxi' => 'ATM DXI',
  'atmFuni' => 'ATM FUNI',
  'atmIma' => 'ATM IMA',
  'pppMultilinkBundle' => 'PPP Multilink Bundle',
  'ipOverCdlc' => 'IBM ipOverCdlc',
  'ipOverClaw' => 'IBM Common Link Access to Workstn',
  'stackToStack' => 'IBM stackToStack',
  'virtualIpAddress' => 'IBM VIPA',
  'mpc' => 'IBM multi-protocol channel support',
  'ipOverAtm' => 'IBM ipOverAtm',
  'iso88025Fiber' => 'ISO 802.5j Fiber Token Ring',
  'tdlc  ' => 'IBM twinaxial data link control',
  'gigabitEthernet' => 'Ethernet',
  'hdlc' => 'HDLC',
  'lapf' => 'LAP F',
  'v37' => 'V.37',
  'x25mlp' => 'Multi-Link Protocol',
  'x25huntGroup' => 'X25 Hunt Group',
  'transpHdlc' => 'Transp HDLC',
  'interleave' => 'Interleave channel',
  'fast' => 'Fast channel',
  'ip' => 'IP',
  'docsCableMaclayer' => 'CATV Mac Layer',
  'docsCableDownstream' => 'CATV Downstream interface',
  'docsCableUpstream' => 'CATV Upstream interface',
  'a12MppSwitch' => 'Avalon Parallel Processor',
  'tunnel' => 'Tunnel',
  'coffee' => 'coffee pot',
  'ces' => 'Circuit Emulation Service',
  'atmSubInterface' => 'ATM Sub Interface',
  'l2vlan' => 'L2 VLAN (802.1Q)',
  'l3ipvlan' => 'L3 VLAN (IP)',
  'l3ipxvlan' => 'L3 VLAN (IPX)',
  'digitalPowerline' => 'IP over Power Lines',
  'mediaMailOverIp' => 'Multimedia Mail over IP',
  'dtm' => 'Dynamic Syncronous Transfer Mode',
  'dcn' => 'Data Communications Network',
  'ipForward' => 'IP Forwarding Interface',
  'msdsl' => 'Multi-rate Symmetric DSL',
  'ieee1394' => 'IEEE1394 High Performance Serial Bus',
  'if-gsn--HIPPI-6400 ',
  'dvbRccMacLayer' => 'DVB-RCC MAC Layer',
  'dvbRccDownstream' => 'DVB-RCC Downstream Channel',
  'dvbRccUpstream' => 'DVB-RCC Upstream Channel',
  'atmVirtual' => 'ATM Virtual Interface',
  'mplsTunnel' => 'MPLS Tunnel Virtual Interface',
  'srp' => 'Spatial Reuse Protocol       ',
  'voiceOverAtm' => 'Voice Over ATM',
  'voiceOverFrameRelay' => 'Voice Over FR',
  'idsl' => 'DSL over ISDN',
  'compositeLink' => 'Avici Composite Link Interface',
  'ss7SigLink' => 'SS7 Signaling Link ',
  'propWirelessP2P' => 'Prop. P2P wireless interface',
  'frForward' => 'Frame Forward Interface',
  'rfc1483       ' => 'Multiprotocol over ATM AAL5',
  'usb' => 'USB Interface',
  'ieee8023adLag' => '802.3ad LAg',
  'bgppolicyaccounting' => 'BGP Policy Accounting',
  'frf16MfrBundle' => 'FRF .16 Multilink Frame Relay ',
  'h323Gatekeeper' => 'H323 Gatekeeper',
  'h323Proxy' => 'H323 Proxy',
  'mpls' => 'MPLS ',
  'mfSigLink' => 'Multi-frequency signaling link',
  'hdsl2' => 'High Bit-Rate DSL - 2nd generation',
  'shdsl' => 'Multirate HDSL2',
  'ds1FDL' => 'Facility Data Link 4Kbps on a DS1',
  'pos' => 'Packet over SONET/SDH Interface',
  'dvbAsiIn' => 'DVB-ASI Input',
  'dvbAsiOut' => 'DVB-ASI Output ',
  'plc' => 'Power Line Communtications',
  'nfas' => 'Non Facility Associated Signaling',
  'tr008' => 'TR008',
  'gr303RDT' => 'Remote Digital Terminal',
  'gr303IDT' => 'Integrated Digital Terminal',
  'isup' => 'ISUP',
  'propDocsWirelessMaclayer' => 'Cisco proprietary Maclayer',
  'propDocsWirelessDownstream' => 'Cisco proprietary Downstream',
  'propDocsWirelessUpstream' => 'Cisco proprietary Upstream',
  'hiperlan2' => 'HIPERLAN Type 2 Radio Interface',
  'propBWAp2Mp' => 'PropBroadbandWirelessAccesspt2multipt',
  'sonetOverheadChannel' => 'SONET Overhead Channel',
  'digitalWrapperOverheadChannel' => 'Digital Wrapper',
  'aal2' => 'ATM adaptation layer 2',
  'radioMAC' => 'MAC layer over radio links',
  'atmRadio' => 'ATM over radio links',
  'imt' => 'Inter Machine Trunks',
  'mvl' => 'Multiple Virtual Lines DSL',
  'reachDSL' => 'Long Reach DSL',
  'frDlciEndPt' => 'Frame Relay DLCI End Point',
  'atmVciEndPt' => 'ATM VCI End Point',
  'opticalChannel' => 'Optical Channel',
  'opticalTransport' => 'Optical Transport',
  'propAtm' => 'Proprietary ATM',
  'voiceOverCable' => 'Voice Over Cable',
  'infiniband' => 'Infiniband',
  'teLink' => 'TE Link',
  'q2931' => 'Q.2931',
  'virtualTg' => 'Virtual Trunk Group',
  'sipTg' => 'SIP Trunk Group',
  'sipSig' => 'SIP Signaling',
  'docsCableUpstreamChannel' => 'CATV Upstream Channel',
  'econet' => 'Acorn Econet',
  'pon155' => 'FSAN 155Mb Symetrical PON',
  'pon622' => 'FSAN 622Mb Symetrical PON',
  'bridge' => 'Transparent bridge interface',
  'linegroup' => 'Interface common to multiple lines',
  'voiceEMFGD' => 'voice E&M Feature Group D',
  'voiceFGDEANA' => 'voice FGD Exchange Access North American',
  'voiceDID' => 'voice Direct Inward Dialing',
  'mpegTransport' => 'MPEG transport interface',
  'sixToFour' => '6to4 interface',
  'gtp' => 'GTP (GPRS Tunneling Protocol)',
  'pdnEtherLoop1' => 'Paradyne EtherLoop 1',
  'pdnEtherLoop2' => 'Paradyne EtherLoop 2',
  'opticalChannelGroup' => 'Optical Channel Group',
  'homepna' => 'HomePNA ITU-T G.989',
  'gfp' => 'GFP',
  'ciscoISLvlan' => 'ISL VLAN',
  'actelisMetaLOOP' => 'MetaLOOP',
  'fcipLink' => 'FCIP Link ',
  'rpr' => 'Resilient Packet Ring Interface Type',
  'qam' => 'RF Qam Interface',
  'lmp' => 'Link Management Protocol',
  'cblVectaStar' => 'Cambridge Broadband Networks Limited VectaStar',
  'docsCableMCmtsDownstream' => 'CATV Modular CMTS Downstream Interface',
  'adsl2' => 'Asymmetric Digital Subscriber Loop Version 2 ',
  'macSecControlledIF' => 'MACSecControlled ',
  'macSecUncontrolledIF' => 'MACSecUncontrolled',
  'aviciOpticalEther' => 'Avici Optical Ethernet Aggregate',
  'atmbond' => 'atmbond',
  'voiceFGDOS' => 'voice FGD Operator Services',
  'mocaVersion1' => 'MultiMedia over Coax Alliance (MoCA) Interface',
  'ieee80216WMAN' => 'IEEE 802.16 WMAN interface',
  'adsl2plus' => 'Asymmetric Digital Subscriber Loop Version 2, ',
  'dvbRcsMacLayer' => 'DVB-RCS MAC Layer',
  'dvbTdm' => 'DVB Satellite TDM',
  'dvbRcsTdma' => 'DVB-RCS TDMA',
  'x86Laps' => 'LAPS based on ITU-T X.86/Y.1323',
  'wwanPP' => '3GPP WWAN',
  'wwanPP2' => '3GPP2 WWAN',
  'voiceEBS' => 'voice P-phone EBS physical interface',
  'ifPwType' => 'Pseudowire',
  'ilan' => 'Internal LAN on a bridge per IEEE 802.1ap',
  'pip' => 'Provider Instance Port IEEE 802.1ah PBB',
  'aluELP' => 'A-Lu ELP',
  'gpon' => 'GPON',
  'vdsl2' => 'VDSL2)',
  'capwapDot11Profile' => 'WLAN Profile',
  'capwapDot11Bss' => 'WLAN BSS',
  'capwapWtpVirtualRadio' => 'WTP Virtual Radio',
  'bits' => 'bitsport',
  'docsCableUpstreamRfPort' => 'DOCSIS CATV Upstream RF',
  'cableDownstreamRfPort' => 'CATV Downstream RF',
  'vmwareVirtualNic' => 'VMware Virtual NIC',
  'ieee802154' => 'IEEE 802.15.4 WPAN',
  'otnOdu' => 'OTN ODU',
  'otnOtu' => 'OTN OTU',
  'ifVfiType' => 'VPLS Forwarding Instance',
  'g9981' => 'G.998.1 Bonded',
  'g9982' => 'G.998.2 Bonded',
  'g9983' => 'G.998.3 Bonded',
  'aluEpon' => 'EPON',
  'aluEponOnu' => 'EPON ONU',
  'aluEponPhysicalUni' => 'EPON Physical UNI',
  'aluEponLogicalLink' => 'EPON Logical Link',
  'aluGponOnu' => 'GPON ONU',
  'aluGponPhysicalUni' => 'GPON Physical UNI',
  'vmwareNicTeam' => 'VMware NIC Team',
);

$rewrite_ifname = array(
  'ether' => 'Ether',
  'gig' => 'Gig',
  'fast' => 'Fast',
  'ten' => 'Ten',
  '-802.1q vlan subif' => '',
  '-802.1q' => '',
  'bvi' => 'BVI',
  'vlan' => 'Vlan',
  'ether' => 'Ether',
  'tunnel' => 'Tunnel',
  'serial' => 'Serial',
  '-aal5 layer' => ' aal5',
  'null' => 'Null',
  'atm' => 'ATM',
  'port-channel' => 'Port-Channel',
  'dial' => 'Dial',
  'hp procurve switch software loopback interface' => 'Loopback Interface',
  'control plane interface' => 'Control Plane',
  'loopback' => 'Loopback',
  '802.1q encapsulation tag' => 'Vlan',
);

$rewrite_ifname_regexp = array(
  '/Nortel .* Module - /i' => '',
  '/Baystack .* - /i' => ''
);

$rewrite_shortif = array(
  'tengigabitethernet' => 'Te',
  'tengige' => 'Te',
  'gigabitethernet' => 'Gi',
  'fortygige' => 'Fo',
  'fastethernet' => 'Fa',
  'ethernet' => 'Et',
  'management' => 'Mgmt',
  'serial' => 'Se',
  'pos' => 'Pos',
  'port-channel' => 'Po',
  'bundle-ether' => 'BE',
  'atm' => 'Atm',
  'null' => 'Null',
  'loopback' => 'Lo',
  'dialer' => 'Di',
  'vlan' => 'Vlan',
  'tunnel' => 'Tu',
  'serviceinstance' => 'SI',
  'dwdm' => 'DWDM',
);

$rewrite_adslLineType = array(
  'noChannel'          => 'No Channel',
  'fastOnly'           => 'Fastpath',
  'interleavedOnly'    => 'Interleaved',
  'fastOrInterleaved'  => 'Fast/Interleaved',
  'fastAndInterleaved' => 'Fast+Interleaved'
);

$rewrite_hrDevice = array (
  'GenuineIntel:' => '',
  'AuthenticAMD:' => '',
  'Intel(R)' => '',
  'CPU' => '',
  '(R)' => '',
  '  ' => ' ',
);

// Rewrite functions

/**
 * Rewrites device hardware based on device os/sysObjectID and hw definitions
 *
 * @param array $device Device array required keys -> os, sysObjectID
 * @param string $sysObjectID_new If passed, than use "new" sysObjectID instead from device array
 * @return string Device hw name or empty string
 */
function rewrite_definition_hardware($device, $sysObjectID_new = NULL)
{
  if (isset($GLOBALS['config']['os'][$device['os']]['model']))
  {
    $model = $GLOBALS['config']['os'][$device['os']]['model'];
    if ($sysObjectID_new && preg_match('/^\.\d[\d\.]+$/', $sysObjectID_new))
    {
      $sysObjectID = $sysObjectID_new;
    }
    else if (preg_match('/^\.\d[\d\.]+$/', $device['sysObjectID']))
    {
      $sysObjectID = $device['sysObjectID'];
    } else {
      // Just random non empty string
      $sysObjectID = 'WRONG_ID_3948ffakc';
    }
    krsort($GLOBALS['config']['model'][$model]); // Resort array by key with high to low order!
    foreach ($GLOBALS['config']['model'][$model] as $key => $entry)
    {
      if (isset($entry['name']) && strpos($sysObjectID, $key) === 0)
      {
        return $entry['name'];
        break;
      }
    }
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_fortinet_hardware($oid)
{

  global $rewrite_fortinet_hardware;

  $hardware = $rewrite_fortinet_hardware[$oid]['name'];

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_extreme_hardware($hardware)
{
  global $rewrite_extreme_hardware;

  $hardware = $rewrite_extreme_hardware[$hardware];

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_ftos_hardware($hardware)
{
  global $rewrite_ftos_hardware;

  $hardware = $rewrite_ftos_hardware[$hardware];

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_cpqida_hardware($hardware)
{
  global $rewrite_cpqida_hardware;

  $hardware = array_str_replace($rewrite_cpqida_hardware, $hardware);

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_ironware_hardware($oid)
{
  global $rewrite_ironware_hardware;

  $hardware = $rewrite_ironware_hardware[$oid]['name'];

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_liebert_hardware($hardware)
{
  global $rewrite_liebert_hardware;

  if (isset($rewrite_liebert_hardware[$hardware]))
  {
    $hardware = $rewrite_liebert_hardware[$hardware]['name'];
  }

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_junose_hardware($hardware)
{
  global $rewrite_junos_hardware;

  $hardware = $rewrite_junos_hardware[$hardware];
  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_junos_hardware($hardware)
{
  global $rewrite_junos_hardware;

  $hardware = $rewrite_junos_hardware[$hardware];
  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_breeze_type($type)
{
  $type = strtolower($type);
  if (isset($GLOBALS['rewrite_breeze_type'][$type]))
  {
    return $GLOBALS['rewrite_breeze_type'][$type];
  } else {
    return strtoupper($type);
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_unix_hardware($descr, $hw = NULL)
{
  $hardware = (!empty($hw) ? trim($hw): 'Generic');

  if     (preg_match('/i[3456]86/i',    $descr)) { $hardware .= ' x86 [32bit]'; }
  elseif (preg_match('/x86_64|amd64/i', $descr)) { $hardware .= ' x86 [64bit]'; }
  elseif (stristr($descr, 'ppc'))     { $hardware .= ' PPC [32bit]'; }
  elseif (stristr($descr, 'sparc32')) { $hardware .= ' SPARC [32bit]'; }
  elseif (stristr($descr, 'sparc64')) { $hardware .= ' SPARC [32bit]'; }
  elseif (stristr($descr, 'mips64'))  { $hardware .= ' MIPS [64bit]'; }
  elseif (stristr($descr, 'mips'))    { $hardware .= ' MIPS [32bit]'; }
  elseif (stristr($descr, 'armv5'))   { $hardware .= ' ARMv5'; }
  elseif (stristr($descr, 'armv6'))   { $hardware .= ' ARMv6'; }
  elseif (stristr($descr, 'armv7'))   { $hardware .= ' ARMv7'; }
  elseif (stristr($descr, 'armv'))    { $hardware .= ' ARM'; }

  return ($hardware);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_ftos_vlanid($device, $ifindex)
{
  // damn DELL use them one known indexes
  //dot1qVlanStaticName.1107787777 = Vlan 1
  //dot1qVlanStaticName.1107787998 = mgmt
  $ftos_vlan = dbFetchCell('SELECT ifName FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $ifindex));
  list(,$vlanid) = explode(' ', $ftos_vlan);
  return $vlanid;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_iftype($type)
{
  $type = array_key_replace($GLOBALS['rewrite_iftype'], $type);
  return $type;
}

// NOTE. For graphs use $escape = FALSE
// TESTME needs unit testing
function rewrite_ifname($inf, $escape = TRUE)
{
  //$inf = strtolower($inf); // ew. -tom
  $inf = array_str_replace($GLOBALS['rewrite_ifname'], $inf);
  $inf = array_preg_replace($GLOBALS['rewrite_ifname_regexp'], $inf);
  if ($escape) { $inf = escape_html($inf); } // By default use htmlentities

  return $inf;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_adslLineType($adslLineType)
{
  global $rewrite_adslLineType;

  if (isset($rewrite_adslLineType[$adslLineType])) { $adslLineType = $rewrite_adslLineType[$adslLineType]; }
  return($adslLineType);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_hrDevice($dev)
{
  global $rewrite_hrDevice;

  $dev = array_str_replace($rewrite_hrDevice, $dev);
  $dev = preg_replace("/\ +/"," ", $dev);
  $dev = trim($dev);

  return $dev;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function short_hostname($hostname, $len = NULL, $escape = TRUE)
{
  $len = (is_numeric($len) ? (int)$len : (int)$GLOBALS['config']['short_hostname']['length']);

  if (function_exists('custom_shorthost'))
  {
    $short_hostname = custom_shorthost($hostname, $len);
  }
  else if (function_exists('custom_short_hostname'))
  {
    $short_hostname = custom_short_hostname($hostname, $len);
  } else {

    $parts = explode('.', $hostname);
    $short_hostname = $parts[0];
    $i = 1;
    while ($i < count($parts) && strlen($short_hostname.'.'.$parts[$i]) < $len)
    {
      $short_hostname = $short_hostname.'.'.$parts[$i];
      $i++;
    }
  }
  if ($escape) { $short_hostname = escape_html($short_hostname); }

  return $short_hostname;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function short_port_descr($descr, $len = NULL, $escape = TRUE)
{
  $len = (is_numeric($len) ? (int)$len : (int)$GLOBALS['config']['short_port_descr']['length']);

  if (function_exists('custom_short_port_descr'))
  {
    $descr = custom_short_port_descr($descr, $len);
  } else {

    list($descr) = explode("(", $descr);
    list($descr) = explode("[", $descr);
    list($descr) = explode("{", $descr);
    list($descr) = explode("|", $descr);
    list($descr) = explode("<", $descr);
    $descr = truncate(trim($descr), $len, '');
  }
  if ($escape) { $descr = escape_html($descr); }

  return $descr;
}

// NOTE. For graphs use $escape = FALSE
// NOTE. short_ifname() differs from short_port_descr()
// short_ifname('FastEternet0/10') == 'Fa0/10'
// DOCME needs phpdoc block
// TESTME needs unit testing
function short_ifname($if, $len = NULL, $escape = TRUE)
{
  $len = (is_numeric($len) ? (int)$len : FALSE);

  $if = rewrite_ifname($if, $escape);
  // $if = strtolower($if);
  $if = array_str_replace($GLOBALS['rewrite_shortif'], $if);
  if ($len) { $if = truncate($if, $len, ''); }

  return $if;
}

// DOCME needs phpdoc block
function rewrite_entity_name($string)
{
  $string = str_replace("Distributed Forwarding Card", "DFC", $string);
  $string = preg_replace("/7600 Series SPA Interface Processor-/", "7600 SIP-", $string);
  $string = preg_replace("/Rev\.\ [0-9\.]+\ /", "", $string);
  $string = preg_replace("/12000 Series Performance Route Processor/", "12000 PRP", $string);
  $string = preg_replace("/^12000/", "", $string);
  $string = preg_replace("/Gigabit Ethernet/", "GigE", $string);
  $string = preg_replace("/^ASR1000\ /", "", $string);
  //$string = str_replace("Routing Processor", "RP", $string);
  //$string = str_replace("Route Processor", "RP", $string);
  //$string = str_replace("Switching Processor", "SP", $string);
  $string = str_replace("Sub-Module", "Module ", $string);
  $string = str_replace("DFC Card", "DFC", $string);
  $string = str_replace("Centralized Forwarding Card", "CFC", $string);
  $string = str_replace(array('fan-tray'), 'Fan Tray', $string);
  $string = str_replace(array('Temp: ', 'CPU of ', 'CPU ', '(TM)', '(R)'), '', $string);
  $string = str_replace('GenuineIntel Intel', 'Intel', $string);
  $string = preg_replace("/(HP \w+) Switch/", "$1", $string);
  $string = preg_replace("/power[ -]supply( \d+)?(?: (?:module|sensor))?/i", "Power Supply$1", $string);
  $string = preg_replace("/([Vv]oltage|[Tt]ransceiver|[Pp]ower|[Cc]urrent|[Tt]emperature|[Ff]an|input|fail)\ [Ss]ensor/", "$1", $string);
  $string = preg_replace("/^(temperature|voltage|current|power)s?\ /", "", $string);
  $string = preg_replace('/\s{2,}/', ' ', $string);
  $string = preg_replace('/([a-z])([A-Z]{2,})/', '$1 $2', $string); // turn "fixedAC" into "fixed AC"

  return trim($string);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_storage($string)
{
  $string = preg_replace('/.*mounted on: (.*)/', "\\1", $string);                 // JunOS
  $string = preg_replace("/(.*), type: (.*), dev: (.*)/", "\\1", $string);        // FreeBSD: '/mnt/Media, type: zfs, dev: Media'
  $string = preg_replace("/(.*) Label:(.*) Serial Number (.*)/", "\\1", $string); // Windows: E:\ Label:Large Space Serial Number 26ad0d98

  return trim($string);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rewrite_location($location)
{
  global $config, $attribs;

  $location = str_replace(array('\"', '"'), '', $location);

  // Allow override sysLocation from DB.
  if ($attribs['override_sysLocation_bool'])
  {
    $new_location = $attribs['override_sysLocation_string'];
    $by = 'DB override';
  }
  // This will call a user-defineable function to rewrite the location however the user wants.
  if (!isset($new_location) && function_exists('custom_rewrite_location'))
  {
    $new_location = custom_rewrite_location($location);
    $by = 'function custom_rewrite_location()';
  }
  // This uses a statically defined array to map locations.
  if (!isset($new_location) && isset($config['location_map'][$location]))
  {
    $new_location = $config['location_map'][$location];
    $by = '$config[\'location_map\']';
  }

  if (isset($new_location))
  {
    print_debug("sysLocation rewritten from '$location' to '$new_location' by $by.");
    $location = $new_location;
  }
  return $location;
}

// Underlying rewrite functions
// DOCME needs phpdoc block
// TESTME needs unit testing
function array_key_replace($array, $string)
{
  if (array_key_exists($string, $array))
  {
    $string = $array[$string];
  }
  return $string;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function array_str_replace($array, $string)
{
  foreach ($array as $search => $replace)
  {
    $string = str_ireplace($search, $replace, $string);
  }

  return $string;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function array_preg_replace($array, $string)
{
  foreach ($array as $search => $replace)
  {
    $string = preg_replace($search, $replace, $string);
  }

  return $string;
}

// FIXME move somewhere else? definitions?
$countries = array(
  'AF' => 'Afghanistan',
  'AX' => 'Åland Islands',
  'AL' => 'Albania',
  'DZ' => 'Algeria',
  'AS' => 'American Samoa',
  'AD' => 'Andorra',
  'AO' => 'Angola',
  'AI' => 'Anguilla',
  'AQ' => 'Antarctica',
  'AG' => 'Antigua and Barbuda',
  'AR' => 'Argentina',
  'AM' => 'Armenia',
  'AW' => 'Aruba',
  'AU' => 'Australia',
  'AT' => 'Austria',
  'AZ' => 'Azerbaijan',
  'BS' => 'Bahamas',
  'BH' => 'Bahrain',
  'BD' => 'Bangladesh',
  'BB' => 'Barbados',
  'BY' => 'Belarus',
  'BE' => 'Belgium',
  'BZ' => 'Belize',
  'BJ' => 'Benin',
  'BM' => 'Bermuda',
  'BT' => 'Bhutan',
  'BO' => 'Bolivia, Plurinational State of',
  'BA' => 'Bosnia and Herzegovina',
  'BW' => 'Botswana',
  'BV' => 'Bouvet Island',
  'BR' => 'Brazil',
  'IO' => 'British Indian Ocean Territory',
  'BN' => 'Brunei Darussalam',
  'BG' => 'Bulgaria',
  'BF' => 'Burkina Faso',
  'BI' => 'Burundi',
  'KH' => 'Cambodia',
  'CM' => 'Cameroon',
  'CA' => 'Canada',
  'CV' => 'Cape Verde',
  'KY' => 'Cayman Islands',
  'CF' => 'Central African Republic',
  'TD' => 'Chad',
  'CL' => 'Chile',
  'CN' => 'China',
  'CX' => 'Christmas Island',
  'CC' => 'Cocos (Keeling) Islands',
  'CO' => 'Colombia',
  'KM' => 'Comoros',
  'CG' => 'Congo',
  'CD' => 'Congo, the Democratic Republic of the',
  'CK' => 'Cook Islands',
  'CR' => 'Costa Rica',
  'CI' => "Côte d'Ivoire",
  'HR' => 'Croatia',
  'CU' => 'Cuba',
  'CY' => 'Cyprus',
  'CZ' => 'Czech Republic',
  'DK' => 'Denmark',
  'DJ' => 'Djibouti',
  'DM' => 'Dominica',
  'DO' => 'Dominican Republic',
  'EC' => 'Ecuador',
  'EG' => 'Egypt',
  'SV' => 'El Salvador',
  'GQ' => 'Equatorial Guinea',
  'ER' => 'Eritrea',
  'EE' => 'Estonia',
  'ET' => 'Ethiopia',
  'FK' => 'Falkland Islands (Malvinas)',
  'FO' => 'Faroe Islands',
  'FJ' => 'Fiji',
  'FI' => 'Finland',
  'FR' => 'France',
  'GF' => 'French Guiana',
  'PF' => 'French Polynesia',
  'TF' => 'French Southern Territories',
  'GA' => 'Gabon',
  'GM' => 'Gambia',
  'GE' => 'Georgia',
  'DE' => 'Germany',
  'GH' => 'Ghana',
  'GI' => 'Gibraltar',
  'GR' => 'Greece',
  'GL' => 'Greenland',
  'GD' => 'Grenada',
  'GP' => 'Guadeloupe',
  'GU' => 'Guam',
  'GT' => 'Guatemala',
  'GG' => 'Guernsey',
  'GN' => 'Guinea',
  'GW' => 'Guinea-Bissau',
  'GY' => 'Guyana',
  'HT' => 'Haiti',
  'HM' => 'Heard Island and McDonald Islands',
  'VA' => 'Holy See (Vatican City State)',
  'HN' => 'Honduras',
  'HK' => 'Hong Kong',
  'HU' => 'Hungary',
  'IS' => 'Iceland',
  'IN' => 'India',
  'ID' => 'Indonesia',
  'IR' => 'Iran, Islamic Republic of',
  'IQ' => 'Iraq',
  'IE' => 'Ireland',
  'IM' => 'Isle of Man',
  'IL' => 'Israel',
  'IT' => 'Italy',
  'JM' => 'Jamaica',
  'JP' => 'Japan',
  'JE' => 'Jersey',
  'JO' => 'Jordan',
  'KZ' => 'Kazakhstan',
  'KE' => 'Kenya',
  'KI' => 'Kiribati',
  'KP' => "Korea, Democratic People's Republic of",
  'KR' => 'Korea, Republic of',
  'KW' => 'Kuwait',
  'KG' => 'Kyrgyzstan',
  'LA' => "Lao People's Democratic Republic",
  'LV' => 'Latvia',
  'LB' => 'Lebanon',
  'LS' => 'Lesotho',
  'LR' => 'Liberia',
  'LY' => 'Libyan Arab Jamahiriya',
  'LI' => 'Liechtenstein',
  'LT' => 'Lithuania',
  'LU' => 'Luxembourg',
  'MO' => 'Macao',
  'MK' => 'Macedonia, the former Yugoslav Republic of',
  'MG' => 'Madagascar',
  'MW' => 'Malawi',
  'MY' => 'Malaysia',
  'MV' => 'Maldives',
  'ML' => 'Mali',
  'MT' => 'Malta',
  'MH' => 'Marshall Islands',
  'MQ' => 'Martinique',
  'MR' => 'Mauritania',
  'MU' => 'Mauritius',
  'YT' => 'Mayotte',
  'MX' => 'Mexico',
  'FM' => 'Micronesia, Federated States of',
  'MD' => 'Moldova, Republic of',
  'MC' => 'Monaco',
  'MN' => 'Mongolia',
  'ME' => 'Montenegro',
  'MS' => 'Montserrat',
  'MA' => 'Morocco',
  'MZ' => 'Mozambique',
  'MM' => 'Myanmar',
  'NA' => 'Namibia',
  'NR' => 'Nauru',
  'NP' => 'Nepal',
  'NL' => 'Netherlands',
  'AN' => 'Netherlands Antilles',
  'NC' => 'New Caledonia',
  'NZ' => 'New Zealand',
  'NI' => 'Nicaragua',
  'NE' => 'Niger',
  'NG' => 'Nigeria',
  'NU' => 'Niue',
  'NF' => 'Norfolk Island',
  'MP' => 'Northern Mariana Islands',
  'NO' => 'Norway',
  'OM' => 'Oman',
  'PK' => 'Pakistan',
  'PW' => 'Palau',
  'PS' => 'Palestinian Territory, Occupied',
  'PA' => 'Panama',
  'PG' => 'Papua New Guinea',
  'PY' => 'Paraguay',
  'PE' => 'Peru',
  'PH' => 'Philippines',
  'PN' => 'Pitcairn',
  'PL' => 'Poland',
  'PT' => 'Portugal',
  'PR' => 'Puerto Rico',
  'QA' => 'Qatar',
  'RE' => 'Réunion',
  'RO' => 'Romania',
  'RU' => 'Russian Federation',
  'RW' => 'Rwanda',
  'BL' => 'Saint Barthélemy',
  'SH' => 'Saint Helena',
  'KN' => 'Saint Kitts and Nevis',
  'LC' => 'Saint Lucia',
  'MF' => 'Saint Martin (French part)',
  'PM' => 'Saint Pierre and Miquelon',
  'VC' => 'Saint Vincent and the Grenadines',
  'WS' => 'Samoa',
  'SM' => 'San Marino',
  'ST' => 'Sao Tome and Principe',
  'SA' => 'Saudi Arabia',
  'SN' => 'Senegal',
  'RS' => 'Serbia',
  'SC' => 'Seychelles',
  'SL' => 'Sierra Leone',
  'SG' => 'Singapore',
  'SK' => 'Slovakia',
  'SI' => 'Slovenia',
  'SB' => 'Solomon Islands',
  'SO' => 'Somalia',
  'ZA' => 'South Africa',
  'GS' => 'South Georgia and the South Sandwich Islands',
  'ES' => 'Spain',
  'LK' => 'Sri Lanka',
  'SD' => 'Sudan',
  'SR' => 'Suriname',
  'SJ' => 'Svalbard and Jan Mayen',
  'SZ' => 'Swaziland',
  'SE' => 'Sweden',
  'CH' => 'Switzerland',
  'SY' => 'Syrian Arab Republic',
  'TW' => 'Taiwan, Province of China',
  'TJ' => 'Tajikistan',
  'TZ' => 'Tanzania, United Republic of',
  'TH' => 'Thailand',
  'TL' => 'Timor-Leste',
  'TG' => 'Togo',
  'TK' => 'Tokelau',
  'TO' => 'Tonga',
  'TT' => 'Trinidad and Tobago',
  'TN' => 'Tunisia',
  'TR' => 'Turkey',
  'TM' => 'Turkmenistan',
  'TC' => 'Turks and Caicos Islands',
  'TV' => 'Tuvalu',
  'UG' => 'Uganda',
  'UA' => 'Ukraine',
  'AE' => 'United Arab Emirates',
  'GB' => 'United Kingdom',
  'US' => 'United States',
  'UM' => 'United States Minor Outlying Islands',
  'UY' => 'Uruguay',
  'UZ' => 'Uzbekistan',
  'VU' => 'Vanuatu',
  'VE' => 'Venezuela, Bolivarian Republic of',
  'VN' => 'Viet Nam',
  'VG' => 'Virgin Islands, British',
  'VI' => 'Virgin Islands, U.S.',
  'WF' => 'Wallis and Futuna',
  'EH' => 'Western Sahara',
  'YE' => 'Yemen',
  'ZM' => 'Zambia',
  'ZW' => 'Zimbabwe'
);

// DOCME needs phpdoc block
// TESTME needs unit testing
function country_from_code($code)
{
  global $countries;

  $code = strtoupper(trim($code));
  if (array_key_exists($code, $countries))
  {
    return $countries[$code];
  }
  return "Unknown";
}

// EOF
