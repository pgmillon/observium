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

$mib = "PW-STD-MIB";

echo("$mib ");

$pws = snmpwalk_cache_oid($device, "pwID", array(), $mib, mib_dirs());
if ($GLOBALS['snmp_status'] === FALSE)
{
  return;
}

$pws = snmpwalk_cache_oid($device, "pwRowStatus",      $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwName",           $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwType",           $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwDescr",          $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwPsnType",        $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwPeerAddrType",   $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwPeerAddr",       $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwOutboundLabel",  $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwInboundLabel",   $pws, $mib, mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwRemoteIfString", $pws, $mib, mib_dirs());

// For MPLS pseudowires
$pws = snmpwalk_cache_oid($device, "pwMplsLocalLdpID", $pws, "PW-MPLS-STD-MIB", mib_dirs());
$pws = snmpwalk_cache_oid($device, "pwMplsPeerLdpID",  $pws, "PW-MPLS-STD-MIB", mib_dirs());
//echo("PWS_WALK: ".count($pws)."\n"); var_dump($pws);

  foreach ($pws as $pw_id => $pw)
  {
    $peer_addr_type = $pw['pwPeerAddrType'];
    if ($peer_addr_type == "ipv4" || $peer_addr_type == "ipv6") { $peer_addr = hex2ip($pw['pwPeerAddr']); }
    if (!get_ip_version($peer_addr) && $pw['pwMplsPeerLdpID'])
    {
      // Sometime return wrong peer addr (not hex string):
      // pwPeerAddr.8 = "\\<h&"
      $peer_addr = preg_replace('/:\d+$/', '', $pw['pwMplsPeerLdpID']);
    }
    if (get_ip_version($peer_addr))
    {
      $peer_rdns = gethostbyaddr6($peer_addr); // PTR name
      if ($peer_addr_type == 'ipv6')
      {
        $peer_addr = Net_IPv6::uncompress($peer_addr, TRUE);
      }

      // FIXME. Retarded way
      $remote_device = dbFetchCell('SELECT `device_id` FROM `'.$peer_addr_type.'_addresses` AS A, `ports` AS I WHERE A.`'.$peer_addr_type.'_address` = ? AND A.`port_id` = I.`port_id` LIMIT 1;', array($peer_addr));
    } else {
      $peer_addr = ''; // Unset peer address
      print_debug("Not found correct peer address. See snmpwalk for 'pwPeerAddr' and 'pwMplsPeerLdpID'.");
    }
    if (empty($remote_device)) { $remote_device = array('NULL'); }

    $if_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE `ifDescr` = ? AND `device_id` = ? LIMIT 1;', array($pw['pwName'], $device['device_id']));
    if (!is_numeric($if_id) && strpos($pw['pwName'], '_'))
    {
      // IOS-XR some time use '_' instead '/'. http://jira.observium.org/browse/OBSERVIUM-246
      // pwName.3221225526 = TenGigE0_3_0_6.438
      // ifDescr.84 = TenGigE0/3/0/6.438
      $if_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE `ifDescr` = ? AND `device_id` = ? LIMIT 1;', array(str_replace('_', '/', $pw['pwName']), $device['device_id']));
    }
    if (!is_numeric($if_id) && strpos($pw['pwMplsLocalLdpID'], ':'))
    {
      // Last (know) way for detect local port by MPLS LocalLdpID,
      // because IOS-XR some time use Remote IP instead ifDescr in pwName
      // pwName.3221225473 = STRING: 82.209.169.153,3055
      // pwMplsLocalLdpID.3221225473 = STRING: 82.209.169.129:0
      list($local_addr) = explode(':', $pw['pwMplsLocalLdpID']);
      if ($peer_addr_type == 'ipv6')
      {
        $local_addr = Net_IPv6::uncompress($local_addr, TRUE);
      }
      $if_id = dbFetchCell('SELECT `port_id` FROM `'.$peer_addr_type.'_addresses` LEFT JOIN `ports` USING (`port_id`) WHERE `'.$peer_addr_type.'_address` = ? AND `device_id` = ? LIMIT 1;', array($local_addr, $device['device_id']));
    }

    $pws_new = array(
      'device_id'        => $device['device_id'],
      'mib'              => $mib,
      'port_id'          => $if_id,
      'peer_device_id'   => $remote_device,
      'peer_addr'        => $peer_addr,
      'peer_rdns'        => $peer_rdns,
      'pwIndex'          => $pw_id,
      'pwType'           => $pw['pwType'],
      'pwID'             => $pw['pwID'],
      'pwOutboundLabel'  => $pw['pwOutboundLabel'],
      'pwInboundLabel'   => $pw['pwInboundLabel'],
      //'pwMplsPeerLdpID'  => $pw['pwMplsPeerLdpID'],
      'pwPsnType'        => $pw['pwPsnType'],
      //'pwLocalIfMtu'     => $pw['pwLocalIfMtu'],
      //'pwRemoteIfMtu'    => $pw['pwRemoteIfMtu'],
      'pwDescr'          => $pw['pwDescr'],
      'pwRemoteIfString' => $pw['pwRemoteIfString'],
      'pwRowStatus'      => $pw['pwRowStatus'],
    );

    if (!empty($pws_cache['pws_db'][$mib][$pw_id]))
    {
      $pws_old = $pws_cache['pws_db'][$mib][$pw_id];
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

// Clean
unset($pws, $pw, $update_array, $remote_device);

// EOF
