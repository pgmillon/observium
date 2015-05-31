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

if ($config['enable_pseudowires'] && $device['os_group'] == "cisco")
{
  unset($cpw_count);
  unset($cpw_exists);

  echo("Cisco Pseudowires : ");

  // Pre-cache the existing state of pseudowires for this device from the database
  $pws_cache = array();
  $pws_db_raw = dbFetchRows("SELECT * FROM `pseudowires` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($pws_db_raw as $pw_db)
  {
    $pws_cache['pws_db'][$pw_db['cpwVcID']] = $pw_db;
  }
  unset($pws_db_raw); unset($pw_db);

  $pws = snmpwalk_cache_oid($device, "cpwVcID", array(), "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
  if ($GLOBALS['snmp_status'] !== FALSE)
  {
    $pws = snmpwalk_cache_oid($device, "cpwVcName",         $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcType",         $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcDescr",        $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcPsnType",      $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcPeerAddrType", $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcPeerAddr",     $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcLocalIfMtu",   $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));
    $pws = snmpwalk_cache_oid($device, "cpwVcRemoteIfMtu",  $pws, "CISCO-IETF-PW-MIB", mib_dirs('cisco'));

    // For MPLS pseudowires
    $pws = snmpwalk_cache_oid($device, "cpwVcMplsPeerLdpID", $pws, "CISCO-IETF-PW-MPLS-MIB", mib_dirs('cisco'));

    foreach ($pws as $pw_id => $pw)
    {
      $peer_addr_type = $pw['cpwVcPeerAddrType'];
      if ($peer_addr_type == "ipv4" || $peer_addr_type == "ipv6") { $peer_addr = hex2ip($pw['cpwVcPeerAddr']); }
      #if(!empty($pw['cpwVcMplsPeerLdpID'])    { list($peer_addr) = explode(":", $pw['cpwVcMplsPeerLdpID']); }

      $cpw_remote_device = dbFetchCell('SELECT `device_id` FROM `'.$peer_addr_type.'_addresses` AS A, `ports` AS I WHERE A.`'.$peer_addr_type.'_address` = ? AND A.`port_id` = I.`port_id`;', array($peer_addr));
      if (empty($cpw_remote_device)) { $cpw_remote_device = array('NULL'); }
      $if_id = dbFetchCell('SELECT `port_id` FROM `ports` WHERE `ifDescr` = ? AND `device_id` = ?;', array($pw['cpwVcName'], $device['device_id']));
      $pws_new = array('device_id' => $device['device_id'],
                       'port_id' => $if_id,
                       'peer_addr' => $peer_addr,
                       'peer_device_id' => $cpw_remote_device,
                       'peer_ldp_id' => $pw['cpwVcMplsPeerLdpID'],
                       'cpwVcID' => $pw['cpwVcID'],
                       'cpwOid' => $pw_id,
                       'pw_type' => $pw['cpwVcType'],
                       'pw_psntype' => $pw['cpwVcPsnType'],
                       'pw_local_mtu' => $pw['cpwVcLocalIfMtu'],
                       'pw_peer_mtu' => $pw['cpwVcRemoteIfMtu'],
                       'pw_descr' => $pw['cpwVcDescr']);
      if (!empty($pws_cache['pws_db'][$pw['cpwVcID']]))
      {
        $pseudowire_id = $pws_cache['pws_db'][$pw['cpwVcID']]['pseudowire_id'];
        if (empty($pws_cache['pws_db'][$pw['cpwVcID']]['peer_device_id']))
        {
          $pws_cache['pws_db'][$pw['cpwVcID']]['peer_device_id'] = array('NULL');
        }
        $update_array = array();
        var_dump(array_keys($pws_new));
        foreach (array_keys($pws_new) as $column)
        {
          if ($pws_new[$column] != $pws_cache['pws_db'][$pw['cpwVcID']][$column])
          {
            $update_array[$column] = $pws_new[$column];
          }
        }
        if (count($update_array) > 0)
        {
          dbUpdate($update_array, 'pseudowires', '`pseudowire_id` = ?', array($pseudowire_id));
          echo("U");
        } else {
          echo(".");
        }
      }
      else
      {
        $pseudowire_id = dbInsert($pws_new, 'pseudowires');
        echo("+");
      }

      $pws_cache['pws'][$pw['cpwVcID']] = $pseudowire_id;
    }
  }

  // Cycle the list of pseudowires we cached earlier and make sure we saw them again.
  foreach ($pws_cache['pws_db'] as $pw_id => $pw)
  {
    if (empty($pws_cache['pws'][$pw_id]))
    {
      dbDelete('pseudowires', '`pseudowire_id` = ?', array($pw['pseudowire_id']));
    }
  }
  echo("\n");

} # enable_pseudowires + os_group=cisco

// EOF
