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

$mib = "JUNIPER-VPN-MIB";
$flags = OBS_SNMP_ALL ^ OBS_QUOTES_STRIP;

echo("$mib ");

$pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwRowStatus", array(), $mib, NULL, $flags);
if ($GLOBALS['snmp_status'] === FALSE)
{
  return;
}

  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwAssociatedInterface", $pws, $mib, NULL, $flags);

  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwLocalSiteId",      $pws, $mib, NULL, $flags); // pwID
  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwTunnelName",       $pws, $mib, NULL, $flags); // pwDescr
  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwTunnelType",       $pws, $mib, NULL, $flags); // pwPsnType
  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnRemotePeIdAddrType", $pws, $mib, NULL, $flags); // pwPeerAddrType
  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnRemotePeIdAddress",  $pws, $mib, NULL, $flags); // pwPeerAddr
  //$pws = snmpwalk_cache_oid($device, "pwLocalIfMtu",     $pws, $mib, mib_dirs());
  //$pws = snmpwalk_cache_oid($device, "pwRemoteIfMtu",    $pws, $mib, mib_dirs());
  $pws = snmpwalk_cache_threepart_oid($device, "jnxVpnPwRemoteSiteId",     $pws, $mib, NULL, $flags); // pwMplsPeerLdpID

  if (OBS_DEBUG > 1)
  {
    echo("PWS_WALK: ".count($pws)."\n"); print_vars($pws);
  }

  foreach ($pws as $pw_type => $entry)
  {
    foreach ($entry as $pw_name => $entry2)
    {
      foreach ($entry2 as $pw_id => $pw)
      {
        //if (strlen($pw['jnxVpnPwRowStatus']) && $pw['jnxVpnPwRowStatus'] != 'active') { continue; } // Skip inactive (active, notinService, notReady, createAndGo, createAndWait, destroy)

        // Get full index
        $pw_index = snmp_translate('jnxVpnPwRowStatus.'.$pw_type.'."'.$pw_name.'".'.$pw_id, $mib);
        $pw_index = str_replace('.1.3.6.1.4.1.2636.3.26.1.4.1.4.', '', $pw_index);

        $peer_addr_type = $pw['jnxVpnRemotePeIdAddrType'];
        if ($peer_addr_type == "ipv4" || $peer_addr_type == "ipv6") { $peer_addr = hex2ip($pw['jnxVpnRemotePeIdAddress']); }

        if (get_ip_version($peer_addr))
        {
          $peer_rdns = gethostbyaddr6($peer_addr); // PTR name
          if ($peer_addr_type == 'ipv6')
          {
            $peer_addr = Net_IPv6::uncompress($peer_addr, TRUE);
          }

          // FIXME. Retarded way
          $remote_device = dbFetchCell('SELECT `device_id` FROM `'.$peer_addr_type.'_addresses`
                                        LEFT JOIN `ports` USING(`port_id`)
                                        WHERE `'.$peer_addr_type.'_address` = ? LIMIT 1;', array($peer_addr));
        } else {
          $peer_rdns = '';
          $peer_addr = ''; // Unset peer address
          print_debug("Not found correct peer address. See snmpwalk for 'jnxVpnRemotePeIdAddress'.");
        }
        if (empty($remote_device)) { $remote_device = array('NULL'); }

        $port = get_port_by_index_cache($device, $pw['jnxVpnPwAssociatedInterface']);

        if (is_numeric($port['port_id']))
        {
          $if_id = $port['port_id'];
        } else {
          $if_id = get_port_id_by_ifDescr($device['device_id'], $pw_name);
        }

        $pws_new = array(
          'device_id'        => $device['device_id'],
          'mib'              => $mib,
          'port_id'          => $if_id,
          'peer_device_id'   => $remote_device,
          'peer_addr'        => $peer_addr,
          'peer_rdns'        => $peer_rdns,
          'pwIndex'          => $pw_index,
          'pwType'           => $pw_type,
          'pwID'             => $pw_id,
          'pwOutboundLabel'  => $pw['jnxVpnPwLocalSiteId'],
          'pwInboundLabel'   => $pw['jnxVpnPwRemoteSiteId'],
          'pwPsnType'        => ($pw['jnxVpnPwTunnelType'] ? $pw['jnxVpnPwTunnelType'] : 'unknown'),
          //'pwLocalIfMtu'     => $pw['pwLocalIfMtu'],
          //'pwRemoteIfMtu'    => $pw['pwRemoteIfMtu'],
          'pwDescr'          => ($pw['jnxVpnPwTunnelName'] ? $pw['jnxVpnPwTunnelName'] : $pw_name),
          //'pwRemoteIfString' => '',
          'pwRowStatus'      => $pw['jnxVpnPwRowStatus'],
        );
        if (OBS_DEBUG > 1) { print_vars($pws_new); }

        if (!empty($pws_cache['pws_db'][$mib][$pw_index]))
        {
          $pws_old = $pws_cache['pws_db'][$mib][$pw_index];
          $pseudowire_id = $pws_old['pseudowire_id'];
          if (empty($pws_old['peer_device_id']))
          {
            $pws_old['peer_device_id'] = array('NULL');
          }

          $update_array = array();
          //var_dump(array_keys($pws_new));
          foreach (array_keys($pws_new) as $column)
          {
            if ($pws_new[$column] != $pws_old[$column])
            {
              $update_array[$column] = $pws_new[$column];
            }
          }
          if (count($update_array) > 0)
          {
            dbUpdate($update_array, 'pseudowires', '`pseudowire_id` = ?', array($pseudowire_id));
            $GLOBALS['module_stats'][$module]['updated']++; //echo("U");
          } else {
            $GLOBALS['module_stats'][$module]['unchanged']++; //echo(".");
          }

        } else {
          $pseudowire_id = dbInsert($pws_new, 'pseudowires');
          $GLOBALS['module_stats'][$module]['added']++; //echo("+");
        }

        $valid['pseudowires'][$mib][$pseudowire_id] = $pseudowire_id;
      }
    }
  }

// Clean
unset($pws, $pw, $update_array, $remote_device, $flags);

// EOF
