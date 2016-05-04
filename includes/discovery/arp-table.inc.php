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

// IP-MIB contains two tables:
//  ipNetToMediaTable     -- deprecated, as it contains only entries for IPv4.
//  ipNetToPhysicalTable  -- current, address version agnostic table
// IPV6-MIB has been abandoned in favor of the revised IP-MIB, but has one table:
//  ipv6NetToMediaTable
// CISCO-IETF-IP-MIB      -- based on an early draft of the revised IP-MIB
//  cInetNetToMediaTable

unset ($mac_table);

// Caching ifIndex
$query = 'SELECT port_id, ifIndex FROM ports WHERE device_id = ? GROUP BY port_id';
foreach (dbFetchRows($query, array($device['device_id'])) as $entry)
{
  $entry_if = $entry['ifIndex'];
  $interface[$entry_if] = $entry['port_id'];
}

echo("ARP/NDP Tables : ");

/// FIXME. Here necessary to use snmpwalk_cache_oid, but snmpwalk_cache_oid() not support custom options like (-OXqs) for parser. -- mike
/// Duplicate the function to use -OX. The SNMP specific stuff is all elsewhere, so shouldn't be too much duplicated -- adama

// First check IP-MIB::ipNetToPhysicalPhysAddress (IPv4 & IPv6)
//ipNetToPhysicalPhysAddress[5][ipv4]["80.93.52.129"] 0:23:ab:64:d:42
//ipNetToPhysicalPhysAddress[34][ipv6]["2a:01:00:d8:00:00:00:01:00:00:00:00:00:00:00:03"] 0:15:63:e8:fb:31:0:0
$ipNetToPhysicalPhysAddress_oid = snmp_walk($device, 'ipNetToPhysicalPhysAddress', '-OXqs', 'IP-MIB');
if ($ipNetToPhysicalPhysAddress_oid)
{
  $oid_data = $ipNetToPhysicalPhysAddress_oid;
  print_debug("Used IP-MIB::ipNetToPhysicalPhysAddress");
} else {
  $oid_data = '';
  if ($device['os_group'] == 'cisco')
  {
    // Last check CISCO-IETF-IP-MIB::cInetNetToMediaPhysAddress (IPv6 only, Cisco only)
    //cInetNetToMediaPhysAddress[167][ipv6]["20:01:0b:08:0b:08:0b:08:00:00:00:00:00:00:00:b1"] 0:24:c4:db:9b:40:0:0
    $cInetNetToMediaPhysAddress_oid = snmp_walk($device, 'cInetNetToMediaPhysAddress', '-OXqs', 'CISCO-IETF-IP-MIB');
    if ($cInetNetToMediaPhysAddress_oid)
    {
      $oid_data .= $cInetNetToMediaPhysAddress_oid;
      print_debug("Used CISCO-IETF-IP-MIB::cInetNetToMediaPhysAddress");
    }
  } else {
    // Or check IPV6-MIB::ipv6NetToMediaPhysAddress (IPv6 only, deprecated, junos)
    //ipv6NetToMediaPhysAddress[18][fe80:0:0:0:200:ff:fe00:4] 2:0:0:0:0:4
    $ipv6NetToMediaPhysAddress_oid = snmp_walk($device, 'ipv6NetToMediaPhysAddress', '-OXqs', 'IPV6-MIB');
    if ($ipv6NetToMediaPhysAddress_oid)
    {
      $oid_data .= $ipv6NetToMediaPhysAddress_oid;
      print_debug("Used IPV6-MIB::ipv6NetToMediaPhysAddress");
    }
  }
}
if (!strstr($oid_data, 'ipv4'))
{
  // Check IP-MIB::ipNetToMediaPhysAddress (IPv4 only)
  //ipNetToMediaPhysAddress[213][10.0.0.162] 70:81:5:ec:f9:bf
  $ipNetToMediaPhysAddress_oid = snmp_walk($device, 'ipNetToMediaPhysAddress', '-OXqs', 'IP-MIB');
  if ($ipNetToMediaPhysAddress_oid)
  {
    $oid_data .= $ipNetToMediaPhysAddress_oid;
    print_debug("Used IP-MIB::ipNetToMediaPhysAddress");
  }
}
$oid_data = trim($oid_data);

// Caching old ARP/NDP table
$query = 'SELECT mac_id, mac_address, ip_address, ip_version, ifIndex FROM ip_mac AS M
          LEFT JOIN ports AS I ON M.port_id = I.port_id
          WHERE I.device_id = ?';
$cache_arp = dbFetchRows($query, array($device['device_id']));
foreach ($cache_arp as $entry)
{
  $old_if = $entry['ifIndex'];
  $old_mac = $entry['mac_address'];
  $old_address = $entry['ip_address'];
  $old_version = $entry['ip_version'];
  $old_table[$old_if][$old_version][$old_address] = $old_mac;
}
$ipv4_pattern = '/\[(\d+)\](?:\[ipv4\])?\["?([\d\.]+)"?\]\s+([a-f\d]+):([a-f\d]+):([a-f\d]+):([a-f\d]+):([a-f\d]+):([a-f\d]{1,2})/i';
$ipv6_pattern = '/\[(\d+)\](?:\[ipv6\])?\["?([a-f\d:]+)"?\]\s+(?:([a-f\d]+):([a-f\d]+):)?([a-f\d]+):([a-f\d]+):([a-f\d]+):([a-f\d]{1,2})/i';

foreach (explode("\n", $oid_data) as $data)
{
  if (preg_match($ipv4_pattern, $data, $matches))
  {
    $ip = $matches[2];
    $ip_version = 4;
  }
  elseif (preg_match($ipv6_pattern, $data, $matches))
  {
    if (count(explode(':', $matches[2])) === 8)
    {
      $ip = Net_IPv6::uncompress($matches[2], TRUE);
    }
    else
    {
      $ip = hex2ip($matches[2]);
    }
    $ip_version = 6;
  } else {
    // In principle the such shouldn't be.
    continue;
  }
  $if = $matches[1];
  $port_id = $interface[$if];

  if ($ip & $port_id)
  {
    if ($matches[3] === '' && $matches[4] === '')
    {
      // Convert IPv4 to fake MAC for 6to4 tunnels
      //ipNetToPhysicalPhysAddress[27][ipv6]["20:02:c0:58:63:01:00:00:00:00:00:00:00:00:00:00"] 0:0:c0:58
      $matches[3] = 'ff';
      $matches[4] = 'fe';
    }
    $mac = zeropad($matches[3]);
    for ($i = 4; $i <= 8; $i++) { $mac .= ':' . zeropad($matches[$i]); }
    $clean_mac = str_replace(':', '', $mac);

    $mac_table[$if][$ip_version][$ip] = $clean_mac;

    if (isset($old_table[$if][$ip_version][$ip]))
    {
      $old_mac = $old_table[$if][$ip_version][$ip];

      if ($clean_mac != $old_mac && $clean_mac != '00:00:00:00:00:00' && $old_mac != '00:00:00:00:00:00')
      {
        print_debug("Changed MAC address for $ip from ".format_mac($old_mac)." to ".format_mac($clean_mac));
        log_event("MAC changed: $ip : " . format_mac($old_mac) . " -> " . format_mac($clean_mac), $device, "port", $port_id);
        dbUpdate(array('mac_address' => $clean_mac) , 'ip_mac', 'port_id = ? AND ip_address = ?', array($port_id, $ip));
        echo("U");
      } else {
        echo(".");
      }
    } else {
      $params = array(
                      'port_id' => $port_id,
                      'mac_address' => $clean_mac,
                      'ip_address' => $ip,
                      'ip_version' => $ip_version);
      dbInsert($params, 'ip_mac');
      print_debug("Added MAC address ".format_mac($clean_mac)." for $ip");
      //log_event("MAC added: $ip : " . format_mac($clean_mac), $device, "port", $port_id);
      echo("+");
    }
  }
}

// Remove expired ARP/NDP entries
$remove_mac_ids = array();
foreach ($cache_arp as $entry)
{
  $entry_mac_id = $entry['mac_id'];
  $entry_mac = $entry['mac_address'];
  $entry_ip = $entry['ip_address'];
  $entry_version = $entry['ip_version'];
  $entry_if  = $entry['ifIndex'];
  $entry_port_id = $interface[$entry_if];
  if (!isset($mac_table[$entry_if][$entry_version][$entry_ip]))
  {
    $remove_mac_ids[] = $entry_mac_id;
    //dbDelete('ip_mac', 'mac_id = ?', array($entry_mac_id));
    print_debug("Removed MAC address ".format_mac($entry_mac)." for $entry_ip");
    //log_event("MAC removed: $entry_ip : " . format_mac($entry_mac), $device, "port", $entry['port_id']);
    echo("-");
  }
}
if (count($remove_mac_ids))
{
  dbDelete('ip_mac', '1' . generate_query_values($remove_mac_ids, 'mac_id'));
}
echo(PHP_EOL);

unset($interface, $remove_mac_ids);

// EOF
