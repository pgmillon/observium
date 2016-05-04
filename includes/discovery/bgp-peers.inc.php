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

// 'BGP4-MIB', 'CISCO-BGP4-MIB', 'BGP4-V2-MIB-JUNIPER', 'FORCE10-BGP4-V2-MIB', 'ARISTA-BGP4V2-MIB'
if ($config['enable_bgp'] && is_device_mib($device, 'BGP4-MIB')) // Note, BGP4-MIB is main MIB, without it, the rest will not be checked
{
  // Get Local ASN
  $bgpLocalAs = snmp_get($device, 'bgpLocalAs.0', '-OUQvn', 'BGP4-MIB', mib_dirs());

  $vendor_oids = array(
    // Juniper BGP4-V2 MIB
    'BGP4-V2-MIB-JUNIPER' => array('vendor_PeerTable'          => 'jnxBgpM2PeerTable',
                                   'vendor_PeerRemoteAs'       => 'jnxBgpM2PeerRemoteAs',
                                   'vendor_PeerRemoteAddr'     => 'jnxBgpM2PeerRemoteAddr',
                                   'vendor_PeerLocalAddr'      => 'jnxBgpM2PeerLocalAddr',
                                   'vendor_PeerIdentifier'     => 'jnxBgpM2PeerIdentifier',
                                   'vendor_PeerIndex'          => 'jnxBgpM2PeerIndex',
                                   'vendor_PeerRemoteAddrType' => 'jnxBgpM2PeerRemoteAddrType',
                                   'vendor_PrefixCountersSafi' => 'jnxBgpM2PrefixCountersSafi'),
    // Force10 BGP4-V2 MIB
    'FORCE10-BGP4-V2-MIB' => array('vendor_PeerTable'          => 'f10BgpM2PeerTable',
                                   'vendor_PeerRemoteAs'       => 'f10BgpM2PeerRemoteAs',
                                   'vendor_PeerRemoteAddr'     => 'f10BgpM2PeerRemoteAddr',
                                   'vendor_PeerLocalAddr'      => 'f10BgpM2PeerLocalAddr',
                                   'vendor_PeerIdentifier'     => 'f10BgpM2PeerIdentifier',
                                   'vendor_PeerIndex'          => 'f10BgpM2PeerIndex',
                                   'vendor_PeerRemoteAddrType' => 'f10BgpM2PeerRemoteAddrType',
                                   'vendor_PrefixCountersSafi' => 'f10BgpM2PrefixCountersSafi'),

    // Arista BGP4-V2 MIB
    'ARISTA-BGP4V2-MIB'   => array('vendor_PeerTable'          => 'aristaBgp4V2PeerTable',
                                   'vendor_PeerRemoteAs'       => 'aristaBgp4V2PeerRemoteAs',
                                   'vendor_PeerRemoteAddr'     => 'INDEX',
                                   'vendor_PeerLocalAddr'      => 'aristaBgp4V2PeerLocalAddr',
                                   'vendor_PeerIdentifier'     => 'aristaBgp4V2PeerRemoteIdentifier',
                                   'vendor_PeerIndex'          => 'n/a',
                                   'vendor_PeerRemoteAddrType' => 'INDEX',
                                   'vendor_PrefixCountersSafi' => 'aristaBgp4V2PrefixInPrefixes'),
                                   # PrefixCountersSafi is not-accessible in draft-13, but we
                                   # only use the INDEX from it, so use aristaBgp4V2PrefixInPrefixes.
  );

  $vendor_mib = FALSE;
  foreach ($vendor_oids as $v_mib => $v_array)
  {
    if (is_device_mib($device, $v_mib))
    {
      $vendor_mib = $v_mib; // Set to current vendor mib
      //echo(" $v_mib ");
      foreach ($v_array as $v => $val) { $$v = $val; }

      if ($v_mib === 'BGP4-V2-MIB-JUNIPER' && $bgpLocalAs === '0')
      {
        // On JunOS BGP4-MIB::bgpLocalAs.0 is always '0'.
        $j_bgpLocalAs = trim(snmp_walk($device, 'jnxBgpM2PeerLocalAs', '-OUQvn', 'BGP4-V2-MIB-JUNIPER'));
        list($bgpLocalAs) = explode("\n", $j_bgpLocalAs);
      }
      break;
    }
  }
  // Discover BGP peers

  /// NOTE. PeerIdentifier != PeerRemoteAddr

  if (is_numeric($bgpLocalAs) && $bgpLocalAs != '0')
  {
    $bgpLocalAs = snmp_dewrap32bit($bgpLocalAs); // Dewrap for 32bit ASN
    print_cli_data("Local AS", "AS$bgpLocalAs ", 2);

    if ($bgpLocalAs != $device['bgpLocalAs'])
    {
      if (!$device['bgpLocalAs'])
      {
        log_event('BGP Local ASN added: AS' . $bgpLocalAs, $device, 'device', $device['device_id']);
      }
      elseif (!$bgpLocalAs)
      {
        log_event('BGP Local ASN removed: AS' . $device['bgpLocalAs'], $device, 'device', $device['device_id']);
      }
      else
      {
        log_event('BGP ASN changed: AS' . $device['bgpLocalAs'] . ' -> AS' . $bgpLocalAs, $device, 'device', $device['device_id']);
      }
      dbUpdate(array('bgpLocalAs' => $bgpLocalAs) , 'devices', 'device_id = ?', array($device['device_id']));
      print_cli_data("Updated ASN", $device['bgpLocalAs']." -> $bgpLocalAs", 2);
      //print_message('Updated ASN (from '.$device['bgpLocalAs']." -> $bgpLocalAs)");
    }
    print_cli_data_field("Caching", 2);
    print_debug(" BGP4-MIB ");

    $cisco_version = FALSE;
    if (is_device_mib($device, 'CISCO-BGP4-MIB'))
    {
      $cisco_version = 1;
      // Check Cisco cbgpPeer2Table
      $cisco_peers = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteAs', array(), 'CISCO-BGP4-MIB', mib_dirs('cisco'));
      if (count($cisco_peers) > 0)
      {
        echo("CISCO-BGP4-MIB ");
        $cisco_version = 2;
        $cisco_peers = snmpwalk_cache_oid($device, 'cbgpPeer2LocalAddr',        $cisco_peers, 'CISCO-BGP4-MIB', mib_dirs('cisco'));
        $cisco_peers = snmpwalk_cache_oid($device, 'cbgpPeer2RemoteIdentifier', $cisco_peers, 'CISCO-BGP4-MIB', mib_dirs('cisco'));

        print_debug("CISCO-BGP4-MIB Peers: ");
        foreach ($cisco_peers as $peer_ip => $entry)
        {
          list(,$peer_ip) = explode('.', $peer_ip, 2);
          $peer_ip  = hex2ip($peer_ip);
          $local_ip = hex2ip($entry['cbgpPeer2LocalAddr']);
          if ($peer_ip  == '0.0.0.0') { $peer_ip  = ''; }
          $peer_as  = $entry['cbgpPeer2RemoteAs'];
          if (!isset($p_list[$peer_ip][$peer_as]) && $peer_ip != '')
          {
            $p_list[$peer_ip][$peer_as] = 1;
            $peerlist[] = array('id' => $entry['cbgpPeer2RemoteIdentifier'], 'local_ip' => $local_ip, 'ip' => $peer_ip, 'as' => $peer_as);
            print_debug("Found peer IP: $peer_ip (AS$peer_as, LocalIP: $local_ip)");
          } //else { echo('nope'); }
        }
      }
    }

    if ($cisco_version !== 2)
    {
      // All MIBs except CISCO-BGP4-MIB
      $peers_data = snmpwalk_cache_oid($device, 'bgpPeerRemoteAs', array(), 'BGP4-MIB', mib_dirs());
      $peers_data = snmpwalk_cache_oid($device, 'bgpPeerRemoteAddr', $peers_data, 'BGP4-MIB', mib_dirs());
      $peers_data = snmpwalk_cache_oid($device, 'bgpPeerLocalAddr', $peers_data, 'BGP4-MIB', mib_dirs());
      $peers_data = snmpwalk_cache_oid($device, 'bgpPeerIdentifier', $peers_data, 'BGP4-MIB', mib_dirs());
      echo("BGP4-MIB ");
      foreach ($peers_data as $peer)
      {
        $peer_as  = snmp_dewrap32bit($peer['bgpPeerRemoteAs']); // Dewrap for 32bit ASN
        $peer_ip  = $peer['bgpPeerRemoteAddr'];
        $local_ip = $peer['bgpPeerLocalAddr'];
        if ($peer_ip  == '0.0.0.0') { $peer_ip  = ''; }
        if (!isset($p_list[$peer_ip][$peer_as]) && $peer_ip != '')
        {
          print_debug("Found peer IP: $peer_ip (AS$peer_as, LocalIP: $local_ip)");
          $peerlist[] = array('id' => $peer['bgpPeerIdentifier'], 'local_ip' => $local_ip, 'ip' => $peer_ip, 'as' => $peer_as);
          $p_list[$peer_ip][$peer_as] = 1;
        }
      }
    }

    if ($vendor_mib)
    {
      $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerRemoteAs, array(), $vendor_mib);
      if (count($vendor_bgp) > 0)
      {
        echo("$vendor_mib ");
        if ($vendor_PeerRemoteAddr != 'INDEX') {
          $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerRemoteAddr, $vendor_bgp, $vendor_mib);
        }
        $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerLocalAddr,  $vendor_bgp, $vendor_mib);
        $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerIdentifier, $vendor_bgp, $vendor_mib);

        print_debug("$vendor_mib Peers: ");
        foreach ($vendor_bgp as $idx => $entry)
        {
          if ($vendor_PeerRemoteAddr == 'INDEX') {
            $peerIdx = parse_bgpmib_v2_peer_index($idx, $vendor_mib);
            $peer_ip = $peerIdx['peerRemoteAddr'];
          } else {
            $peer_ip = hex2ip($entry[$vendor_PeerRemoteAddr]);
          }
          $local_ip = hex2ip($entry[$vendor_PeerLocalAddr]);
          if ($peer_ip  == '0.0.0.0') { $peer_ip  = ''; }
          $peer_as = $entry[$vendor_PeerRemoteAs];
          if (!isset($p_list[$peer_ip][$peer_as]) && $peer_ip != '')
          {
            $p_list[$peer_ip][$peer_as] = 1;
            $peerlist[] = array('id' => $entry[$vendor_PeerIdentifier], 'local_ip' => $local_ip, 'ip' => $peer_ip, 'as' => $peer_as);
            print_debug("Found peer IP: $peer_ip (AS$peer_as, LocalIP: $local_ip)");
          }
        }
      } else {
        $vendor_mib = FALSE; // Unset vendor_mib since not found on device
      }
    } # Vendors

  } else {
    echo("No BGP on host");
    if (is_numeric($device['bgpLocalAs']))
    {
      log_event('BGP ASN removed: AS' . $device['bgpLocalAs'], $device, 'bgp');
      dbUpdate(array('bgpLocalAs' => array('NULL')) , 'devices', 'device_id = ?', array($device['device_id']));
      print_message('Removed ASN ('.$device['bgpLocalAs'].')');
    } # End if
  } # End if

  // Process discovered peers

  $table_rows = array();

  if (isset($peerlist))
  {
    // Walk vendor oids
    if ($vendor_mib)
    {
      //$vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerRemoteAs,       $vendor_bgp, $vendor_mib);
      if ($vendor_PeerRemoteAddr != 'INDEX')
      {
        //$vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerRemoteAddr,     $vendor_bgp, $vendor_mib);
        $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerRemoteAddrType, $vendor_bgp, $vendor_mib);
      }
      if ($vendor_PeerIndex != 'n/a')
      {
        $vendor_bgp = snmpwalk_cache_oid_num2($device, $vendor_PeerIndex,          $vendor_bgp, $vendor_mib);
      }
      $vendor_counters = snmpwalk_cache_oid_num2($device, $vendor_PrefixCountersSafi,  array(), $vendor_mib);
    }

    echo(PHP_EOL);
    foreach ($peerlist as $peer)
    {
      $astext = get_astext($peer['as']);
      $reverse_dns = gethostbyaddr6($peer['ip']);
      if ($reverse_dns == $peer['ip']) { unset($reverse_dns); }

      // Search remote device if possible
      $peer_addr_type = get_ip_version($peer['ip']);
      if ($peer_addr_type)
      {
        if (in_array($peer['ip'], array('0.0.0.0', '127.0.0.1', '0000:0000:0000:0000:0000:0000:0000:0001', '0000:0000:0000:0000:0000:0000:0000:0000')))
        {
          $ip_array = FALSE;
        } else {
          $peer_addr_type = 'ipv'.$peer_addr_type;
          $query_ip = 'SELECT `device_id`, `port_id`, `ifOperStatus`, `ifAdminStatus` FROM `'.$peer_addr_type.'_addresses`
                       LEFT JOIN `ports` USING(`port_id`)
                       WHERE `'.$peer_addr_type.'_address` = ? AND `device_id` IN
                       (SELECT `device_id` FROM `devices` WHERE `bgpLocalAs` > 0 AND `disabled` = 0)';
          $ip_array = dbFetchRows($query_ip, array($peer['ip']));
        }

        if (count($ip_array) > 1)
        {
          // multiple devices found, heh I not sure
          $peer_device_id = array('NULL');
          foreach ($ip_array as $entry)
          {
            $as_array = dbFetchColumn('SELECT DISTINCT `bgpPeerRemoteAs` FROM `bgpPeers` WHERE `device_id` = ?', array($entry['device_id']));
            if (in_array($bgpLocalAs, $as_array))
            {
              $peer_device_id = $entry['device_id'];
              $peer_device = device_by_id_cache($peer_device_id);
              if ($peer_device['status'] && $entry['ifOperStatus'] == 'up')
              {
                break; // Stop on first UP device/port
              }
            }
          }
        }
        else if ($ip_array)
        {
          // It simple, only one device
          $as_array = dbFetchColumn('SELECT DISTINCT `bgpPeerRemoteAs` FROM `bgpPeers` WHERE `device_id` = ?', array($ip_array[0]['device_id']));
          if (in_array($bgpLocalAs, $as_array))
          {
            $peer_device_id = $ip_array[0]['device_id'];
          }
        } else {
          $peer_device_id = array('NULL');
        }
      }
      if (is_numeric($peer_device_id)) { $peer_device = device_by_id_cache($peer_device_id); } else { unset($peer_device); }

      $table_rows[$peer['ip']] = array($peer['local_ip'], $peer['as'], $peer['ip'], '', $reverse_dns, truncate($peer_device['hostname'], 30));
      $params = array('device_id'         => $device['device_id'],
                      'bgpPeerIdentifier' => $peer['id'],
                      'bgpPeerRemoteAddr' => $peer['ip'],
                      'bgpPeerLocalAddr'  => $peer['local_ip'],
                      'bgpPeerRemoteAs'   => $peer['as'],
                      'astext'            => $astext,
                      'reverse_dns'       => $reverse_dns,
                      'peer_device_id'    => $peer_device_id);
      $peer_db = dbFetchRow('SELECT * FROM `bgpPeers` WHERE `device_id` = ? AND `bgpPeerRemoteAddr` = ?', array($device['device_id'], $peer['ip']));
      if (count($peer_db))
      {
        foreach ($params as $param => $value)
        {
          $update_array = array();
          if ($value === array('NULL'))
          {
            if ($peer_db[$param] != '') { $update_array[$param] = $value; }
          }
          else if ($value != $peer_db[$param])
          {
            $update_array[$param] = $value;
          }
        }
        if (count($update_array))
        {
          dbUpdate($update_array, 'bgpPeers', 'device_id = ? AND bgpPeerRemoteAddr = ?', array($device['device_id'], $peer['ip']));
          $GLOBALS['module_stats'][$module]['updated']++;
        } else {
          $GLOBALS['module_stats'][$module]['unchanged']++;
        }
      } else {
        dbInsert($params, 'bgpPeers');
        $GLOBALS['module_stats'][$module]['added']++;
      }

      // Autodiscovery for ibgp neighbours
      if ($config['autodiscovery']['bgp'] && $peer['as'] == $device['bgpLocalAs'])
      {
        discover_new_device($peer['ip'], 'bgp', 'BGP', $device);
      }
    } # Foreach

    // AFI/SAFI for specific vendors
    if ($cisco_version || $vendor_mib)
    {
      unset($af_list);

      if ($cisco_version)
      {
        // Get afi/safi and populate cbgp on cisco ios (xe/xr)

        if ($cisco_version === 2)
        {
          $af_data = snmpwalk_cache_oid($device, 'cbgpPeer2AddrFamilyName', $cbgp, 'CISCO-BGP4-MIB', mib_dirs('cisco'));
        } else {
          $af_data = snmpwalk_cache_oid($device, 'cbgpPeerAddrFamilyName', $cbgp, 'CISCO-BGP4-MIB', mib_dirs('cisco'));
        }

        foreach ($af_data as $af => $entry)
        {
          if ($cisco_version === 2)
          {
            list(,$af) = explode('.', $af, 2);
            $text = $entity['cbgpPeer2AddrFamilyName'];
          } else {
            $text = $entity['cbgpPeerAddrFamilyName'];
          }
          $afisafi = explode('.', $af);
          $c = count($afisafi);
          $afi = $afisafi[$c - 2];
          $safi = $afisafi[$c - 1];
          $peer_ip = hex2ip(str_replace(".$afi.$safi", '', $af));
          print_debug("Peer IP: $peer_ip, AFI: $afi, SAFI: $safi");
          if ($afi && $safi)
          {
            $af_list[$peer_ip][$afi][$safi] = 1;
            if (strlen($table_rows[$peer_ip][3])) { $table_rows[$peer_ip][3] .= ', '; }
            $table_rows[$peer_ip][3] .= $afi.'.'.$safi;
            if (dbFetchCell('SELECT COUNT(*) FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND `bgpPeerRemoteAddr` = ? AND `afi` = ? AND `safi` = ?', array($device['device_id'], $peer_ip, $afi, $safi)) == 0)
            {
              $params = array('device_id' => $device['device_id'], 'bgpPeerRemoteAddr' => $peer_ip, 'afi' => $afi, 'safi' => $safi);
              dbInsert($params, 'bgpPeers_cbgp');
            }
          }
        }
      } # cisco_version

      if ($vendor_mib)
      {
        // See posible AFI/SAFI here: https://www.juniper.net/techpubs/en_US/junos12.3/topics/topic-map/bgp-multiprotocol.html
        $afis['1'] = 'ipv4';
        $afis['2'] = 'ipv6';
        $afis['ipv4'] = '1';
        $afis['ipv6'] = '2';
        $safis = array(1 => 'unicast',
                       2 => 'multicast',
                       128 => 'vpn');

        //print_vars($vendor_counters);
        foreach ($vendor_bgp as $idx => $entry)
        {
          if ($vendor_PeerRemoteAddr == 'INDEX')
          {
            $peerIdx = parse_bgpmib_v2_peer_index($idx, $vendor_mib);
            $afi = $peerIdx['peerRemoteAddrFamily'];
            $peer_ip = $peerIdx['peerRemoteAddr'];
            $index = $idx;
          } else {
            $peer_ip = hex2ip($entry[$vendor_PeerRemoteAddr]);
            $afi = $entry[$vendor_PeerRemoteAddrType];
            $index = $entry[$vendor_PeerIndex];
          }
          $peer_as = $entry[$vendor_PeerRemoteAs];

          foreach ($safis as $i => $safi)
          {
            if (isset($vendor_counters[$index.'.'.$afi.".$i"]) || isset($vendor_counters[$index.'.'.$afis[$afi].".$i"]))
            {
              if (is_numeric($afi)) { $afi = $afis[$afi]; }
              print_debug("INDEX: $index, AS: $peer_as, IP: $peer_ip, AFI: $afi, SAFI: $safi");
              if (OBS_DEBUG > 1) { var_dump($entry); }
              $af_list[$peer_ip][$afi][$safi] = 1;

              if (strlen($table_rows[$peer_ip][3])) { $table_rows[$peer_ip][3] .= ', '; }
              $table_rows[$peer_ip][3] .= $afi.'.'.$safi;

              if (dbFetchCell('SELECT COUNT(*) FROM `bgpPeers_cbgp` WHERE `device_id` = ? AND `bgpPeerRemoteAddr` = ? AND `afi` = ? AND `safi` = ?', array($device['device_id'], $peer_ip, $afi, $safi)) == 0)
              {
                $params = array('device_id' => $device['device_id'], 'bgpPeerRemoteAddr' => $peer_ip, 'bgpPeerIndex' => $index, 'afi' => $afi, 'safi' => $safi);
                dbInsert($params, 'bgpPeers_cbgp');
              }
              else if ($index >= 0)
              {
                // Update Index
                $params = array('device_id' => $device['device_id'], 'bgpPeerRemoteAddr' => $peer_ip, 'afi' => $afi, 'safi' => $safi);
                dbUpdate(array('bgpPeerIndex' => $index), 'bgpPeers_cbgp', 'device_id = ? AND `bgpPeerRemoteAddr` = ? AND `afi` = ? AND `safi` = ?', array($device['device_id'], $peer_ip, $afi, $safi));
              }
            } else {
              print_debug("Did not find $index.$afi.$i or $index.$afis[$afi].$i");
            }
          }
        }
      } # Vendors

      // Remove deleted AFI/SAFI
      unset($afi, $safi, $peer_ip);
      $query = 'SELECT * FROM bgpPeers_cbgp WHERE `device_id` = ?';
      foreach (dbFetchRows($query, array($device['device_id'])) as $entry)
      {
        $peer_ip = $entry['bgpPeerRemoteAddr'];
        $afi = $entry['afi'];
        $safi = $entry['safi'];
        $cbgp_id = $entry['cbgp_id'];
        if (!isset($af_list[$peer_ip][$afi][$safi]))
        {
          dbDelete('bgpPeers_cbgp', '`cbgp_id` = ?', array($cbgp_id));
          dbDelete('bgpPeers_cbgp-state', '`cbgp_id` = ?', array($cbgp_id));
        }
      } # AF list
    } # os=cisco|some vendors

  } # isset

  // Delete removed peers
  unset($peer_ip, $peer_as);
  $query = 'SELECT * FROM bgpPeers WHERE device_id = ?';
  foreach (dbFetchRows($query, array($device['device_id'])) as $entry)
  {
    $peer_ip = $entry['bgpPeerRemoteAddr'];
    $peer_as = $entry['bgpPeerRemoteAs'];

    if (!isset($p_list[$peer_ip][$peer_as]))
    {
      dbDelete('bgpPeers', '`bgpPeer_id` = ?', array($entry['bgpPeer_id']));
      dbDelete('bgpPeers-state', '`bgpPeer_id` = ?', array($entry['bgpPeer_id']));
      $GLOBALS['module_stats'][$module]['deleted']++;
    }
  }

  $table_headers = array('%WLocal IP%n', '%WPeer: AS%n', '%WIP%n', '%WFamily%n', '%WrDNS%n', '%WRemote Device%n');
  print_cli_table($table_rows, $table_headers);

  unset($p_list, $peerlist, $vendor_mib, $cisco_version, $cisco_peers, $table_rows, $table_headers);
}

// EOF
