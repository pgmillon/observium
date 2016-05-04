<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME - seems to be broken. IPs appear with leading zeroes.
// FIXME. Missing clean DB

if (is_device_mib($device, 'CISCO-IPSEC-FLOW-MONITOR-MIB'))
{
  // Cache DB entries
  $tunnels_db = dbFetchRows("SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($tunnels_db as $tunnel) { $tunnels[$tunnel['peer_addr']] = $tunnel;}

  $device_context = $device;
  if (!count($tunnels_db))
  {
    // Set retries to 0 for speedup first walking, only if previously polling also empty (DB empty)
    $device_context['snmp_retries'] = 0;
  }
  $ike_array   = snmpwalk_cache_oid($device_context, "cikeTunnelEntry", array(), "CISCO-IPSEC-FLOW-MONITOR-MIB", mib_dirs('cisco'));
  unset($device_context);
  if ($GLOBALS['snmp_status'])
  {
    $ipsec_array = snmpwalk_cache_oid($device, "cipSecTunnelEntry", array(), "CISCO-IPSEC-FLOW-MONITOR-MIB", mib_dirs('cisco'));
  }

  foreach ($ipsec_array as $index => $tunnel)
  {
    $tunnel = array_merge($tunnel, $ike_array[$tunnel['cipSecTunIkeTunnelIndex']]);

    echo("Tunnel $index (".$tunnel['cipSecTunIkeTunnelIndex'].")\n");

    echo("Address ".$tunnel['cikeTunRemoteValue']."\n");

    $address = $tunnel['cikeTunRemoteValue'];

    $oids = array (
      "cipSecTunInOctets",
      "cipSecTunInDecompOctets",
      "cipSecTunInPkts",
      "cipSecTunInDropPkts",
      "cipSecTunInReplayDropPkts",
      "cipSecTunInAuths",
      "cipSecTunInAuthFails",
      "cipSecTunInDecrypts",
      "cipSecTunInDecryptFails",
      "cipSecTunOutOctets",
      "cipSecTunOutUncompOctets",
      "cipSecTunOutPkts",
      "cipSecTunOutDropPkts",
      "cipSecTunOutAuths",
      "cipSecTunOutAuthFails",
      "cipSecTunOutEncrypts",
      "cipSecTunOutEncryptFails");

    $db_oids = array("cipSecTunStatus" => "tunnel_status",
                     "cikeTunLocalName" => "tunnel_name",
                     "cikeTunLocalValue" => "local_addr");

    if (!is_array($tunnels[$tunnel['cikeTunRemoteValue']]))
    {
      $tunnel_id = dbInsert(array('device_id' => $device['device_id'],
                                  'peer_addr' => $tunnel['cikeTunRemoteValue'],
                                  'local_addr' => $tunnel['cikeTunLocalValue'],
                                  'tunnel_name' => $tunnel['cikeTunLocalName']), 'ipsec_tunnels');
    } else {
      foreach ($db_oids as $db_oid => $db_value)
      {
        $db_update[$db_value] = $tunnel[$db_oid];
      }

      $updated   = dbUpdate($db_update, 'ipsec_tunnels', '`tunnel_id` = ?', array($tunnels[$tunnel['cikeTunRemoteValue']]['tunnel_id']));
    }

    if (is_numeric($tunnel['cipSecTunHcInOctets']) && is_numeric($tunnel['cipSecTunHcInDecompOctets']) &&
        is_numeric($tunnel['cipSecTunHcOutOctets']) && is_numeric($tunnel['cipSecTunHcOutUncompOctets']))
    {
      echo("HC ");

      $tunnel['cipSecTunInOctets'] = $tunnel['cipSecTunHcInOctets'];
      $tunnel['cipSecTunInDecompOctets'] = $tunnel['cipSecTunHcInDecompOctets'];
      $tunnel['cipSecTunOutOctets'] = $tunnel['cipSecTunHcOutOctets'];
      $tunnel['cipSecTunOutUncompOctets'] = $tunnel['cipSecTunHcOutUncompOctets'];
    }

    $rrd_file = "ipsectunnel-".$address.".rrd";

    $rrd_create = '';

    foreach ($oids as $oid)
    {
      $oid_ds = truncate(str_replace("cipSec", "", $oid), 19, '');
      $rrd_create .= ' DS:'.$oid_ds.':COUNTER:600:U:1000000000';
    }

    $rrdupdate = "N";

    foreach ($oids as $oid)
    {
      if (is_numeric($tunnel[$oid]))
      {
        $value = $tunnel[$oid];
      } else {
        $value = "0";
      }
      $rrdupdate .= ':'.$value;
    }

    if (isset($tunnel['cikeTunRemoteValue']))
    {
      rrdtool_create($device, $rrd_file, $rrd_create);
      rrdtool_update($device, $rrd_file, $rrdupdate);
      #$graphs['ipsec_tunnels'] = TRUE;
    }

  }

  unset($oids, $data, $oid, $tunnel);
}

// EOF
