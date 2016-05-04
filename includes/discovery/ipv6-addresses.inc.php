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

echo("IPv6 Addresses : ");

$ip_version = 'ipv6';

// Get IP addresses from IP-MIB
$oids_ip = array('ipAddressIfIndex', 'ipAddressPrefix', 'ipAddressOrigin');
//ipAddressIfIndex.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = 1
//ipAddressPrefix.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = ipAddressPrefixOrigin.1.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01".128
//ipAddressOrigin.ipv6."00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:01" = manual
//Origins: 1:other, 2:manual, 4:dhcp, 5:linklayer, 6:random
$oid_data = array();
foreach ($oids_ip as $oid)
{
  $oid_data = snmpwalk_cache_oid($device, $oid.'.'.$ip_version, $oid_data, 'IP-MIB', mib_dirs());
}

// Rewrite IP-MIB array
$ip_data = array();
$check_ipv6_mib = FALSE; // Flag for additionally check IPv6-MIB
foreach ($oid_data as $key => $entry)
{
  $ip_address = hex2ip(str_replace($ip_version.'.', '', $key));
  $ifIndex = $entry['ipAddressIfIndex'];
  if ($entry['ipAddressPrefix'] == 'zeroDotZero')
  {
    // Additionally walk IPV6-MIB, especially in JunOS because they spit at world standards
    // See: http://jira.observium.org/browse/OBSERVIUM-1271
    $check_ipv6_mib = TRUE;
  }
  $entry['ipAddressPrefix'] = end(explode('.', $entry['ipAddressPrefix']));
  if (!is_numeric($entry['ipAddressPrefix'])) { $entry['ipAddressPrefix'] = '128'; }
  if (is_ipv6_valid($ip_address, $entry['ipAddressPrefix']) === FALSE) { continue; }
  foreach ($oids_ip as $oid)
  {
    $ip_data[$ifIndex][$ip_address][$oid] = $entry[$oid];
  }
}
if (OBS_DEBUG && $ip_data) { echo "IP-MIB\n"; print_vars($ip_data); }

if (is_device_mib($device, 'CISCO-IETF-IP-MIB') && !count($ip_data))
{
  // Get IP addresses from CISCO-IETF-IP-MIB
  //cIpAddressIfIndex.ipv6."20:01:04:70:00:15:00:bb:00:00:00:00:00:00:00:02" = 450
  //cIpAddressPrefix.ipv6."20:01:04:70:00:15:00:bb:00:00:00:00:00:00:00:02" = cIpAddressPfxOrigin.450.ipv6."20:01:04:70:00:15:00:bb:00:00:00:00:00:00:00:00".64
  //cIpAddressOrigin.ipv6."20:01:04:70:00:15:00:bb:00:00:00:00:00:00:00:02" = manual
  //Origins: 1:other, 2:manual, 4:dhcp, 5:linklayer, 6:random
  $ip_data = array();
  foreach ($oids_ip as $oid)
  {
    $oid_data = snmpwalk_cache_oid($device, 'c'.ucfirst($oid).'.'.$ip_version, $oid_data, 'CISCO-IETF-IP-MIB', mib_dirs('cisco'));
  }

  // Rewrite CISCO-IETF-IP-MIB array
  foreach ($oid_data as $key => $entry)
  {
    $ip_address = hex2ip(str_replace($ip_version.'.', '', $key));
    $ifIndex = $entry['cIpAddressIfIndex'];
    $entry['cIpAddressPrefix'] = end(explode('.', $entry['cIpAddressPrefix']));
    if (!is_numeric($entry['cIpAddressPrefix'])) { $entry['cIpAddressPrefix'] = '128'; }
    if (is_ipv6_valid($ip_address, $entry['cIpAddressPrefix']) === FALSE) { continue; }
    foreach ($oids_ip as $oid)
    {
      $ip_data[$ifIndex][$ip_address][$oid] = $entry['c'.ucfirst($oid)];
    }
  }
  if (OBS_DEBUG && $ip_data) { echo "CISCO-IETF-IP-MIB\n"; print_vars($ip_data); }
}

if ($check_ipv6_mib || !count($ip_data))
{
  // Get IP addresses from IPV6-MIB
  $oids_ipv6 = array('ipv6AddrPfxLength', 'ipv6AddrType');
  //.1.3.6.1.2.1.55.1.8.1.2.6105.16.254.128.0.0.0.0.0.0.2.26.169.255.254.23.134.97 = 64
  //.1.3.6.1.2.1.55.1.8.1.3.6105.16.254.128.0.0.0.0.0.0.2.26.169.255.254.23.134.97 = stateful
  //Types: stateless(1), stateful(2), unknown(3)
  $oid_data = array();
  foreach ($oids_ipv6 as $oid)
  {
    $oid_data = snmpwalk_cache_oid_num2($device, $oid, $oid_data, 'IPV6-MIB', mib_dirs());
  }

  // Rewrite IPV6-MIB array
  foreach ($oid_data as $key => $entry)
  {
    list($ifIndex, $ip_address) = explode('.', $key, 2);
    $ip_address = snmp2ipv6($ip_address);
    if (!is_numeric($entry['ipv6AddrPfxLength'])) { $entry['ipv6AddrPfxLength'] = '128'; }
    if (is_ipv6_valid($ip_address, $entry['ipv6AddrPfxLength']) === FALSE) { continue; }
    $ip_data[$ifIndex][$ip_address]['ipAddressIfIndex'] = $ifIndex;
    $ip_data[$ifIndex][$ip_address]['ipAddressPrefix'] = $entry['ipv6AddrPfxLength'];
    if (!isset($ip_data[$ifIndex][$ip_address]['ipAddressOrigin']))
    {
      $ip_data[$ifIndex][$ip_address]['ipAddressOrigin'] = $entry['ipv6AddrType'];
    }
  }
  if (OBS_DEBUG && $ip_data) { echo "IPV6-MIB\n"; print_vars($ip_data); }
}

// Caching old IPv6 addresses table
$query = 'SELECT * FROM `ipv6_addresses` AS A
          LEFT JOIN `ports` AS I ON A.`port_id` = I.`port_id`
          WHERE I.`device_id` = ?';
foreach (dbFetchRows($query, array($device_id)) as $entry)
{
  $old_table[$entry['ifIndex']][$entry['ipv6_address']] = $entry;
}

// Process found IPv6 addresses
$valid[$ip_version] = array();
$check_networks = array();
if (count($ip_data))
{
  foreach ($ip_data as $ifIndex => $addresses)
  {
    if (!isset($cache['port_index'][$device_id][$ifIndex])) { continue; } // continue if ifIndex not found
    $port_id = $cache['port_index'][$device_id][$ifIndex];
    foreach ($addresses as $ipv6_address => $entry)
    {
      $update_array = array();
      $ipv6_prefixlen = $entry['ipAddressPrefix'];
      $ipv6_origin = $entry['ipAddressOrigin'];
      $full_address = $ipv6_address.'/'.$ipv6_prefixlen;
      $ipv6_network = Net_IPv6::getNetmask($full_address) . '/' . $ipv6_prefixlen;
      $ipv6_compressed = Net_IPv6::compress($ipv6_address);
      $full_compressed = $ipv6_compressed.'/'.$ipv6_prefixlen;
      // First check networks
      $ipv6_network_id = dbFetchCell('SELECT `ipv6_network_id` FROM `ipv6_networks` WHERE `ipv6_network` = ?', array($ipv6_network));
      if (empty($ipv6_network_id))
      {
        $ipv6_network_id = dbInsert(array('ipv6_network' => $ipv6_network), 'ipv6_networks');
        echo('N');
      }
      // Check IPs in DB
      if (isset($old_table[$ifIndex][$ipv6_address]))
      {
        foreach (array('ipv6_prefixlen', 'ipv6_origin', 'ipv6_network_id', 'port_id') as $param)
        {
          if ($old_table[$ifIndex][$ipv6_address][$param] != $$param) { $update_array[$param] = $$param; }
        }
        if (count($update_array))
        {
          // Updated
          dbUpdate($update_array, 'ipv6_addresses', '`ipv6_address_id` = ?', array($old_table[$ifIndex][$ipv6_address]['ipv6_address_id']));
          if (isset($update_array['port_id']))
          {
            log_event("IPv6 removed: $ipv6_compressed/".$old_table[$ifIndex][$ipv6_address]['ipv6_prefixlen'], $device, 'port', $old_table[$ifIndex][$ipv6_address]['port_id']);
            log_event("IPv6 added: $full_compressed", $device, 'port', $port_id);
          }
          else if (isset($update_array['ipv6_prefixlen']))
          {
            log_event("IPv6 changed: $ipv6_compressed/".$old_table[$ifIndex][$ipv6_address]['ipv6_prefixlen']." -> $full_compressed", $device, 'port', $port_id);
          }
          echo('U');
          $check_networks[$ipv6_network_id] = 1;
        } else {
          // Not changed
          echo('.');
        }
      } else {
        // New IP
        foreach (array('ipv6_address', 'ipv6_compressed', 'ipv6_prefixlen', 'ipv6_origin', 'ipv6_network_id', 'port_id') as $param)
        {
          $update_array[$param] = $$param;
        }
        dbInsert($update_array, 'ipv6_addresses');
        log_event("IPv6 added: $full_compressed", $device, 'port', $port_id);
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
  $full_address = $entry['ipv6_address'] . '/' . $entry['ipv6_prefixlen'];
  $port_id = $entry['port_id'];
  $valid_address = $full_address  . '-' . $port_id;
  if (!isset($valid[$ip_version][$valid_address]))
  {
    // Delete IP
    dbDelete('ipv6_addresses', '`ipv6_address_id` = ?', array($entry['ipv6_address_id']));
    log_event("IPv6 removed: $full_address", $device, 'port', $port_id);
    echo('-');
    $check_networks[$entry['ipv6_network_id']] = 1;
  }
}
// Clean networks
if (count($check_networks))
{
  foreach ($check_networks as $network_id => $n)
  {
    $count = dbFetchCell('SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_network_id` = ?', array($network_id));
    if (empty($count))
    {
      dbDelete('ipv6_networks', '`ipv6_network_id` = ?', array($network_id));
      echo('n');
    }
  }
}

echo(PHP_EOL);

// EOF
