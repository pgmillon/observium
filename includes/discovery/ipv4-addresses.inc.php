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

global $cache;

$device_id = $device['device_id'];
// Caching ifIndex
//FIXME. Need common caching
$query = 'SELECT `port_id`, `ifIndex` FROM `ports` WHERE `device_id` = ? GROUP BY `port_id`';
foreach (dbFetchRows($query, array($device_id)) as $entry)
{
  $entry_if = $entry['ifIndex'];
  if (is_numeric($entry['port_id'])) { $cache['port_index'][$device_id][$entry_if] = $entry['port_id']; }
}

echo("IPv4 Addresses : ");

$ip_version = 'ipv4';

// NOTE. By default used old tables IP-MIB, because some weird vendors use "random" data in new tables:
//ipAddressIfIndex.ipv4."94.142.242.194" = 2
//ipAddressIfIndex.ipv4."127.0.0.1" = 1
//ipAddressPrefix.ipv4."94.142.242.194" = ipAddressPrefixOrigin.2.ipv4."88.0.0.0".5
//ipAddressPrefix.ipv4."127.0.0.1" = ipAddressPrefixOrigin.1.ipv4."51.101.48.0".0

// Get IP addresses from IP-MIB
$oids_ip = array('ipAdEntIfIndex', 'ipAdEntNetMask');
//IP-MIB::ipAdEntIfIndex.10.0.0.130 = 193
//IP-MIB::ipAdEntNetMask.10.0.0.130 = 255.255.255.252
//IP-MIB::ipAdEntIfIndex.4.10.44.44.110 = 151192525
//IP-MIB::ipAdEntNetMask.4.10.44.44.110 = 255.255.255.0
$oid_data = array();
foreach ($oids_ip as $oid)
{
  $oid_data = snmpwalk_cache_oid($device, $oid, $oid_data, 'IP-MIB', mib_dirs());
}

// Rewrite IP-MIB array
$ip_data = array();
foreach ($oid_data as $ip_address => $entry)
{
  $ifIndex = $entry['ipAdEntIfIndex'];
  $ip_address_fix = explode('.', $ip_address);
  if (count($ip_address_fix) == 5)
  {
    unset($ip_address_fix[0]);
    $ip_address = implode('.', $ip_address_fix);
  }
  $ip_mask_fix = explode('.', $entry['ipAdEntNetMask']);
  if ($ip_mask_fix[0] < 255 && $ip_mask_fix[1] <= '255' && $ip_mask_fix[2] <= '255' && $ip_mask_fix[3] == '255')
  {
    // On some D-Link used wrong masks: 252.255.255.255, 0.255.255.255
    $entry['ipAdEntNetMask'] = $ip_mask_fix[3] . '.' . $ip_mask_fix[2] . '.' . $ip_mask_fix[1] . '.' . $ip_mask_fix[0];
  }
  if (is_ipv4_valid($ip_address, $entry['ipAdEntNetMask']) !== FALSE)
  {
    $ip_data[$ifIndex][$ip_address] = $entry;
  }
}
if (OBS_DEBUG && $ip_data) { echo "IP-MIB\n"; print_vars($ip_data); }

if (!count($ip_data))
{
  // Get IP addresses from IP-MIB (new)
  $oids_ip = array('ipAddressIfIndex', 'ipAddressType', 'ipAddressPrefix', 'ipAddressOrigin');
  //IP-MIB::ipAddressIfIndex.ipv4."198.237.180.2" = 8
  //IP-MIB::ipAddressPrefix.ipv4."198.237.180.2" = ipAddressPrefixOrigin.8.ipv4."198.237.180.2".32
  //IP-MIB::ipAddressOrigin.ipv4."198.237.180.2" = manual
  //Origins: 1:other, 2:manual, 4:dhcp, 5:linklayer, 6:random
  $oid_data = array();
  foreach ($oids_ip as $oid)
  {
    $oid_data = snmpwalk_cache_oid($device, $oid.'.'.$ip_version, $oid_data, 'IP-MIB', mib_dirs());
  }

  // Rewrite IP-MIB array
  $ip_data = array();
  foreach ($oid_data as $key => $entry)
  {
    if ($entry['ipAddressType'] == 'broadcast') { continue; } // Skip broadcasts
    $ip_address = str_replace($ip_version.'.', '', $key);
    $ifIndex = $entry['ipAddressIfIndex'];
    $entry['ipAddressPrefix'] = end(explode('.', $entry['ipAddressPrefix']));
    if (!is_numeric($entry['ipAddressPrefix'])) { $entry['ipAddressPrefix'] = '32'; }
    if (is_ipv4_valid($ip_address, $entry['ipAddressPrefix']) !== FALSE)
    {
      foreach ($oids_ip as $oid)
      {
        $ip_data[$ifIndex][$ip_address][$oid] = $entry[$oid];
      }
    }
  }
  if (OBS_DEBUG && $ip_data) { echo "IP-MIB\n"; print_vars($ip_data); }
}

// Caching old IPv4 addresses table
$query = 'SELECT * FROM `ipv4_addresses` AS A
          LEFT JOIN `ports` AS I ON A.`port_id` = I.`port_id`
          WHERE I.`device_id` = ?';
foreach (dbFetchRows($query, array($device_id)) as $entry)
{
  $old_table[$entry['ifIndex']][$entry['ipv4_address']] = $entry;
}

// Process founded IPv4 addresses
$valid[$ip_version] = array();
$check_networks = array();
if (count($ip_data))
{
  foreach ($ip_data as $ifIndex => $addresses)
  {
    if (!isset($cache['port_index'][$device_id][$ifIndex])) { continue; } // continue if ifIndex not found
    $port_id = $cache['port_index'][$device_id][$ifIndex];
    foreach ($addresses as $ipv4_address => $entry)
    {
      $update_array = array();
      $ipv4_prefix = ($entry['ipAddressPrefix'] ? $entry['ipAddressPrefix'] : $entry['ipAdEntNetMask']);
      if (empty($ipv4_prefix))
      {
        // Fix for some retard devices, which not return IP masks
        $ipv4_prefix = '255.255.255.255';
      }
      $addr = Net_IPv4::parseAddress($ipv4_address.'/'.$ipv4_prefix);
      $ipv4_prefixlen = $addr->bitmask;
      $ipv4_network = $addr->network . '/' . $ipv4_prefixlen;
      $full_address = $ipv4_address . '/' . $ipv4_prefixlen;

      // First check networks
      $ipv4_network_id = dbFetchCell('SELECT `ipv4_network_id` FROM `ipv4_networks` WHERE `ipv4_network` = ?', array($ipv4_network));
      if (empty($ipv4_network_id))
      {
        $ipv4_network_id = dbInsert(array('ipv4_network' => $ipv4_network), 'ipv4_networks');
        echo('N');
      }
      // Check IPs in DB
      if (isset($old_table[$ifIndex][$ipv4_address]))
      {
        foreach (array('ipv4_prefixlen', 'ipv4_network_id', 'port_id') as $param)
        {
          if ($old_table[$ifIndex][$ipv4_address][$param] != $$param) { $update_array[$param] = $$param; }
        }
        if (count($update_array))
        {
          // Updated
          dbUpdate($update_array, 'ipv4_addresses', '`ipv4_address_id` = ?', array($old_table[$ifIndex][$ipv4_address]['ipv4_address_id']));
          if (isset($update_array['port_id']))
          {
            log_event("IPv4 removed: $ipv4_address/".$old_table[$ifIndex][$ipv4_address]['ipv4_prefixlen'], $device, 'port', $old_table[$ifIndex][$ipv4_address]['port_id']);
            log_event("IPv4 added: $full_address", $device, 'port', $port_id);
          } else {
            log_event("IPv4 changed: $ipv4_address/".$old_table[$ifIndex][$ipv4_address]['ipv4_prefixlen']." -> $full_address", $device, 'port', $port_id);
          }
          echo('U');
          $check_networks[$ipv4_network_id] = 1;
        } else {
          // Not changed
          echo('.');
        }
      } else {
        // New IP
        foreach (array('ipv4_address', 'ipv4_prefixlen', 'ipv4_network_id', 'port_id') as $param)
        {
          $update_array[$param] = $$param;
        }
        dbInsert($update_array, 'ipv4_addresses');
        log_event("IPv4 added: $full_address", $device, 'port', $port_id);
        echo('+');
      }
      $valid_address = $full_address . '-' . $port_id;
      $valid[$ip_version][$valid_address] = 1;
    }
  }
}

// Refetch and clean IP addresses from DB
foreach (dbFetchRows($query, array($device_id)) as $entry)
{
  $full_address = $entry['ipv4_address'] . '/' . $entry['ipv4_prefixlen'];
  $port_id = $entry['port_id'];
  $valid_address = $full_address  . '-' . $port_id;
  if (!isset($valid[$ip_version][$valid_address]))
  {
    // Delete IP
    dbDelete('ipv4_addresses', '`ipv4_address_id` = ?', array($entry['ipv4_address_id']));
    log_event("IPv4 removed: $full_address", $device, 'port', $port_id);
    echo('-');
    $check_networks[$entry['ipv4_network_id']] = 1;
  }
}
// Clean networks
if (count($check_networks))
{
  foreach ($check_networks as $network_id => $n)
  {
    $count = dbFetchCell('SELECT COUNT(*) FROM `ipv4_addresses` WHERE `ipv4_network_id` = ?', array($network_id));
    if (empty($count))
    {
      dbDelete('ipv4_networks', '`ipv4_network_id` = ?', array($network_id));
      echo('n');
    }
  }
}

echo(PHP_EOL);

// EOF
