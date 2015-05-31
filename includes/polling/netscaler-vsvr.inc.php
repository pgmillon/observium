<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/// VServers

## NS-ROOT-MIB::vsvrName."observium" = STRING: "observium"
## NS-ROOT-MIB::vsvrIpAddress."observium" = IpAddress: 195.78.84.141
## NS-ROOT-MIB::vsvrPort."observium" = INTEGER: 80
## NS-ROOT-MIB::vsvrType."observium" = INTEGER: http(0)
## NS-ROOT-MIB::vsvrState."observium" = INTEGER: up(7)
## NS-ROOT-MIB::vsvrCurClntConnections."observium" = Gauge32: 18
## NS-ROOT-MIB::vsvrCurSrvrConnections."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrSurgeCount."observium" = Counter32: 0
## NS-ROOT-MIB::vsvrTotalRequests."observium" = Counter64: 64532
## NS-ROOT-MIB::vsvrTotalRequestBytes."observium" = Counter64: 22223153
## NS-ROOT-MIB::vsvrTotalResponses."observium" = Counter64: 64496
## NS-ROOT-MIB::vsvrTotalResponseBytes."observium" = Counter64: 1048603453
## NS-ROOT-MIB::vsvrTotalPktsRecvd."observium" = Counter64: 629637
## NS-ROOT-MIB::vsvrTotalPktsSent."observium" = Counter64: 936237
## NS-ROOT-MIB::vsvrTotalSynsRecvd."observium" = Counter64: 43130
## NS-ROOT-MIB::vsvrCurServicesDown."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrCurServicesUnKnown."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrCurServicesOutOfSvc."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrCurServicesTransToOutOfSvc."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrCurServicesUp."observium" = Gauge32: 0
## NS-ROOT-MIB::vsvrTotMiss."observium" = Counter64: 0
## NS-ROOT-MIB::vsvrRequestRate."observium" = STRING: "0"
## NS-ROOT-MIB::vsvrRxBytesRate."observium" = STRING: "248"
## NS-ROOT-MIB::vsvrTxBytesRate."observium" = STRING: "188"
## NS-ROOT-MIB::vsvrSynfloodRate."observium" = STRING: "0"
## NS-ROOT-MIB::vsvrIp6Address."observium" = STRING: 0:0:0:0:0:0:0:0
## NS-ROOT-MIB::vsvrTotHits."observium" = Counter64: 64537
## NS-ROOT-MIB::vsvrTotSpillOvers."observium" = Counter32: 0
## NS-ROOT-MIB::vsvrTotalClients."observium" = Counter64: 43023
## NS-ROOT-MIB::vsvrClientConnOpenRate."observium" = STRING: "0"

if ($device['os'] == "netscaler")
{

  /// Services <> Vservers

  echo("Netscaler services <> vservers\n");

  echo(str_pad("VServer", 25) . " | " . str_pad("Service",25) . " | " .  str_pad("Type",16) ." | ". str_pad("Weight",16) . "\n".
       str_pad("", 90, "-")."\n");

  $sv_db    = dbFetchRows("SELECT * FROM `netscaler_services_vservers` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($sv_db as $sv) { $svs_db[$sv['vsvr_name']][$sv['svc_name']] = $sv; $svs_exist[$sv['sv_id']] = array('vsvr_name' => $sv['vsvr_name'], 'svc_name' => $sv['svc_name']); }
  if ($debug) { print_vars($svs_db); }

  $svc_vsvrs = snmp_walk_parser($device, "vserverServiceEntry", 3, "NS-ROOT-MIB", mib_dirs('citrix'));
  foreach ($svc_vsvrs as $vserver => $svs)
  {
    foreach ($svs as $service => $sv)
    {
      echo(str_pad($vserver, 25) . " | " . str_pad($service,25) . " | " .  str_pad($sv['vsvrServiceEntityType'],16) ." | ". str_pad($sv['serviceWeight'],16));

      if (is_array($svs_db[$vserver][$service]))
      {
        /// FIXME Update Code
        dbUpdate(array('service_weight' => $sv['serviceWeight']), 'netscaler_services_vservers', '`device_id` = ? AND `vsvr_name` = ? AND `svc_name` = ?', array($device['device_id'], $vserver, $service));
        echo("U");
        unset($svs_exist[$svs_db[$vserver][$service]['sv_id']]);
      } else {
        dbInsert(array('device_id' => $device['device_id'], 'vsvr_name' => $vserver, 'svc_name' => $service, 'service_weight' => $sv['serviceWeight']), 'netscaler_services_vservers');
        echo("+");
      }
      echo("\n");
    }
  }

    if ($debug) { print_vars($vsvr_exist); }

  foreach ($svs_exist as $sv_id => $sv)
  {
    echo("-".$sv['vsvr_name']."/".$sv['svc_name']." ");
    dbDelete('netscaler_services_vservers', "`sv_id` =  ?", array($sv_id));
  }

  echo("\n");

  /// VServers

  echo("Netscaler VServers\n");

  $oids_gauge   = array('vsvrCurClntConnections','vsvrCurSrvrConnections');
  $oids_counter = array('vsvrSurgeCount','vsvrTotalRequests','vsvrTotalRequestBytes','vsvrTotalResponses','vsvrTotalResponseBytes','vsvrTotalPktsRecvd',
                        'vsvrTotalPktsSent','vsvrTotalSynsRecvd','vsvrTotMiss','vsvrTotHits','vsvrTotSpillOvers','vsvrTotalClients');

  $oids = array_merge($oids_gauge, $oids_counter);
  unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

  foreach ($oids_gauge as $oid)
  {
    $oid_ds = truncate(str_replace("vsvr", "", $oid), 19, '');
    $rrd_create .= " DS:$oid_ds:GAUGE:600:U:100000000000";
  }

  foreach ($oids_counter as $oid)
  {
    $oid_ds = truncate(str_replace("vsvr", "", $oid), 19, '');
    $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
  }

  $vsvr_array = snmpwalk_cache_oid($device, "vserverEntry", array(), "NS-ROOT-MIB", mib_dirs('citrix'));

  $vsvr_db    = dbFetchRows("SELECT * FROM `netscaler_vservers` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($vsvr_db as $vsvr) { $vsvrs[$vsvr['vsvr_name']] = $vsvr; }
  if ($debug) { print_vars($vsvrs); }

  foreach ($vsvr_array as $index => $vsvr)
  {
    // Rename rrds to match vsvrName as that's how things are indexed.
    if (isset($vsvr['vsvrFullName']))
    {
      $vsvr['label'] = $vsvr['vsvrFullName'];
      $rrd_file = "netscaler-vsvr-".$vsvr['vsvrName'].".rrd";
      $rrd_file_old = "netscaler-vsvr-".$vsvr['vsvrFullName'].".rrd";
      if (is_file(get_rrd_path($device, $rrd_file_old))) { rename(get_rrd_path($device, $rrd_file_old), get_rrd_path($device, $rrd_file)); } // CLEANME remove in r6000
    } else {
      $vsvr['label'] = $vsvr['vsvrName'];
    }

    if (isset($vsvr['vsvrName']))
    {
      $vsvr_exist[$vsvr['vsvrName']] = 1;
      $rrd_file = "netscaler-vsvr-".$vsvr['vsvrName'].".rrd";
      $rrdupdate = "N";

      foreach ($oids as $oid)
      {
        if (is_numeric($vsvr[$oid]))
        {
          $rrdupdate .= ":".$vsvr[$oid];
        } else {
          $rrdupdate .= ":U";
        }
      }

      echo(str_pad($vsvr['vsvrName'], 25) . " | " . str_pad($vsvr['vsvrType'],5) . " | " .  str_pad($vsvr['vsvrState'],6) ." | ". str_pad($vsvr['vsvrIpAddress'],16) ." | ". str_pad($vsvr['vsvrPort'],5));
      echo(" | " . str_pad($vsvr['vsvrRequestRate'],8) . " | " . str_pad($vsvr['vsvrRxBytesRate']."B/s", 8)." | ". str_pad($vsvr['vsvrTxBytesRate']."B/s", 8));

      $db_update = array('vsvr_label' => $vsvr['label'], 'vsvr_fullname' => $vsvr['vsvrFullName'], 'vsvr_ip' => $vsvr['vsvrIpAddress'], 'vsvr_ipv6' => $vsvr['vsvrIp6Address'], 'vsvr_port' => $vsvr['vsvrPort'], 'vsvr_state' => $vsvr['vsvrState'], 'vsvr_type' => $vsvr['vsvrType'],
                         'vsvr_entitytype' => $vsvr['vsvrEntityType'], 'vsvr_req_rate' => $vsvr['RequestRate'], 'vsvr_bps_in' => $vsvr['vsvrRxBytesRate'], 'vsvr_bps_out' => $vsvr['vsvrTxBytesRate']);

     if (!is_array($vsvrs[$vsvr['vsvrName']]))
     {
       $db_insert = array_merge(array('device_id' => $device['device_id'], 'vsvr_name' => $vsvr['vsvrName']), $db_update);
       $vsvr_id = dbInsert($db_insert, 'netscaler_vservers'); echo(" +");
     } else {
       $updated  = dbUpdate($db_update, 'netscaler_vservers', '`vsvr_id` = ?', array($vsvrs[$vsvr['vsvrName']]['vsvr_id']));
       echo(" U");

       // Check Alerts
       check_entity('netscaler_vsvr', $vsvrs[$vsvr['vsvrName']], array('vsvr_state' => $vsvr['vsvrState'],
                                                                       'vsvr_bps_in' => $vsvr['vsvrRxBytesRate'],
                                                                       'vsvr_bps_out' => $vsvr['vsvrTxBytesRate']));

     }

     rrdtool_create($device, $rrd_file, $rrd_create);
     rrdtool_update($device, $rrd_file, $rrdupdate);

     echo("\n");
    }

  }

  if ($debug) { print_vars($vsvr_exist); }

  foreach ($vsvrs as $db_name => $db_id)
  {
    if (!$vsvr_exist[$db_name])
    {
      echo("-".$db_name);
      dbDelete('netscaler_vservers', "`vsvr_id` =  ?", array($db_id));
    }
  }

  echo("\n");

/// Services

## NS-ROOT-MIB::svcServiceName."http81_observium-server" = STRING: "http81_observium-server"
## NS-ROOT-MIB::svcIpAddress."http81_observium-server" = IpAddress: 46.105.127.13
## NS-ROOT-MIB::svcPort."http81_observium-server" = INTEGER: 81
## NS-ROOT-MIB::svcServiceType."http81_observium-server" = INTEGER: http(0)
## NS-ROOT-MIB::svcState."http81_observium-server" = INTEGER: up(7)
## NS-ROOT-MIB::svcMaxReqPerConn."http81_observium-server" = INTEGER: 0
// NS-ROOT-MIB::svcAvgTransactionTime."http81_observium-server" = Wrong Type (should be Timeticks): INTEGER: 137870
// NS-ROOT-MIB::svcEstablishedConn."http81_observium-server" = Counter32: 4
// NS-ROOT-MIB::svcActiveConn."http81_observium-server" = Gauge32: 3
// NS-ROOT-MIB::svcSurgeCount."http81_observium-server" = Counter32: 0
// NS-ROOT-MIB::svcTotalRequests."http81_observium-server" = Counter64: 3227
// NS-ROOT-MIB::svcTotalRequestBytes."http81_observium-server" = Counter64: 1947816
// NS-ROOT-MIB::svcTotalResponses."http81_observium-server" = Counter64: 3227
// NS-ROOT-MIB::svcTotalResponseBytes."http81_observium-server" = Counter64: 43924021
// NS-ROOT-MIB::svcTotalPktsRecvd."http81_observium-server" = Counter64: 37739
// NS-ROOT-MIB::svcTotalPktsSent."http81_observium-server" = Counter64: 23972
// NS-ROOT-MIB::svcTotalSynsRecvd."http81_observium-server" = Counter64: 0
## NS-ROOT-MIB::svcGslbSiteName."http81_observium-server" = STRING: "N/A"
// NS-ROOT-MIB::svcAvgSvrTTFB."http81_observium-server" = Gauge32: 0
// NS-ROOT-MIB::svctotalJsTransactions."http81_observium-server" = Counter64: 0
// NS-ROOT-MIB::svcdosQDepth."http81_observium-server" = Counter32: 0
// NS-ROOT-MIB::svcCurClntConnections."http81_observium-server" = Gauge32: 3
## NS-ROOT-MIB::svcRequestRate."http81_observium-server" = STRING: "0"
## NS-ROOT-MIB::svcRxBytesRate."http81_observium-server" = STRING: "0"
## NS-ROOT-MIB::svcTxBytesRate."http81_observium-server" = STRING: "0"
## NS-ROOT-MIB::svcSynfloodRate."http81_observium-server" = STRING: "0"
## NS-ROOT-MIB::svcTicksSinceLastStateChange."http81_observium-server" = Timeticks: (371894) 1:01:58.94
// NS-ROOT-MIB::svcTotalClients."http81_observium-server" = Counter64: 3228
// NS-ROOT-MIB::svcTotalServers."http81_observium-server" = Counter64: 464
## NS-ROOT-MIB::svcMaxClients."http81_observium-server" = INTEGER: 0
// NS-ROOT-MIB::svcActiveTransactions."http81_observium-server" = Gauge32: 3
## NS-ROOT-MIB::svcServiceFullName."http81_observium-server" = STRING: "http81_observium-server"
## NS-ROOT-MIB::svcInetAddressType."http81_observium-server" = INTEGER: ipv4(1)
## NS-ROOT-MIB::svcInetAddress."http81_observium-server" = Hex-STRING: 2E 69 7F 0D
## NS-ROOT-MIB::monsvcServiceName."http81_observium-server"."tcp-default" = STRING: "http81_observium-server"
## NS-ROOT-MIB::monitorRTO."http81_observium-server"."tcp-default" = Gauge32: 0
## NS-ROOT-MIB::monitorState."http81_observium-server"."tcp-default" = INTEGER: monitorStateUp(7)
## NS-ROOT-MIB::drtmRTO."http81_observium-server"."tcp-default" = Gauge32: 0
## NS-ROOT-MIB::drtmLearningProbes."http81_observium-server"."tcp-default" = Gauge32: 0
## NS-ROOT-MIB::monitorCurFailedCount."http81_observium-server"."tcp-default" = Gauge32: 0
## NS-ROOT-MIB::monitorWeight."http81_observium-server"."tcp-default" = INTEGER: 1
## NS-ROOT-MIB::monitorProbes."http81_observium-server"."tcp-default" = Counter32: 329
## NS-ROOT-MIB::monitorFailed."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorMaxClient."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedCon."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedCode."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedStr."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedTimeout."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedSend."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedFTP."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedPort."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedResponse."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorFailedId."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorProbesNoChange."http81_observium-server"."tcp-default" = Counter32: 0
## NS-ROOT-MIB::monitorResponseTimeoutThreshExceed."http81_observium-server"."tcp-default" = Counter32: 0

  echo("Netscaler Services\n");

  $oids = array('svcActiveConn:G','svcActiveTransactions:G','svcAvgTransactionTime:G',
                'svcEstablishedConn:C', 'svcSurgeCount:C', 'svcTotalRequests:C', 'svcTotalRequestBytes:C',
                'svcTotalResponses:C', 'svcTotalResponseBytes:C', 'svcTotalPktsRecvd:C', 'svcTotalPktsSent:C', 'svcTotalSynsRecvd:C',
                'svcTotalClients:C', 'svcTotalServers:C', 'svcAvgSvrTTFB:G', 'svcCurClntConnections:G', 'svctotalJsTransactions:C',
                'svcdosQDepth:C');

##  $oids = array_merge($oids_gauge, $oids_counter);

  unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

  foreach ($oids as $oid)
  {
    list($oid, $type) = explode(":", $oid);
    if ($type == "G")
    {
      $oid_ds = truncate(str_replace("svc", "", $oid), 19, '');
      $rrd_create .= " DS:$oid_ds:GAUGE:600:U:100000000000";
    } elseif ($type == "C") {
      $oid_ds = truncate(str_replace("svc", "", $oid), 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
    }
  }

  $svc_array = snmpwalk_cache_oid($device, "serviceEntry", array(), "NS-ROOT-MIB", mib_dirs('citrix'));

  $svc_db    = dbFetchRows("SELECT * FROM `netscaler_services` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($svc_db as $svc) { $svcs[$svc['svc_name']] = $svc; }
  if ($debug) { print_vars($svcs); }

  foreach ($svc_array as $index => $svc)
  {
    // Use svcServiceFullName when it exists.
    /// This is cosmetic only, retain svcServiceName for indexing !
    if (isset($svc['svcServiceFullName']))
    {
      $svc['label'] = $svc['svcServiceFullName'];
    } else {
      $svc['label'] = $svc['svcServiceName'];
    }

    if (isset($svc['svcServiceName']))
    {
      $svc_exist[$svc['svcServiceName']] = 1;
      $rrd_file = "nscaler-svc-".$svc['svcServiceName'].".rrd";
      $rrdupdate = "N";

      foreach ($oids as $oid)
      {
        list($oid, $type) = explode(":", $oid);
        if (is_numeric($svc[$oid]))
        {
          $rrdupdate .= ":".$svc[$oid];
        } else {
          $rrdupdate .= ":U";
        }
      }

      echo(str_pad($svc['svcServiceName'], 25) . " | " . str_pad($svc['svcServiceType'],15) . " | " .  str_pad($svc['svcState'],6) ." | ". str_pad($svc['svcIpAddress'],16) ." | ". str_pad($svc['svcPort'],5));
      echo(" | " . str_pad($svc['svcRequestRate'],8) . " | " . str_pad($svc['svcRxBytesRate']."B/s", 8)." | ". str_pad($svc['svcTxBytesRate']."B/s", 8));

      $db_update = array('svc_label' => $svc['label'], 'svc_fullname' => $svc['svcServiceFullName'], 'svc_ip' => $svc['svcIpAddress'], 'svc_port' => $svc['svcPort'], 'svc_state' => $svc['svcState'], 'svc_type' => $svc['svcServiceType'],
                         'svc_req_rate' => $svc['RequestRate'], 'svc_bps_in' => $svc['svcRxBytesRate'], 'svc_bps_out' => $svc['svcTxBytesRate']);

     if (!is_array($svcs[$svc['svcServiceName']]))
     {
       $db_insert = array_merge(array('device_id' => $device['device_id'], 'svc_name' => $svc['svcServiceName']), $db_update);
       $svc_id = dbInsert($db_insert, 'netscaler_services'); echo(" +");
     } else {
       $updated  = dbUpdate($db_update, 'netscaler_services', '`svc_id` = ?', array($svcs[$svc['svcServiceName']]['svc_id']));
       echo(" U");

       // Check Alerts
       check_entity('netscaler_svc', $svcs[$svc['svcServiceName']], array('svc_state' => $svc['svcState'], 'svc_bps_in' => $svc['svcRxBytesRate'], 'svc_bps_out' => $svc['svcTxBytesRate']));

     }

     rrdtool_create($device, $rrd_file, $rrd_create);
     rrdtool_update($device, $rrd_file, $rrdupdate);

     echo("\n");
    }

  }

  if ($debug) { print_vars($svc_exist); }

  foreach ($svcs as $db_name => $db_id)
  {
    if (!$svc_exist[$db_name])
    {
      echo("-".$db_name);
      dbDelete('netscaler_services', "`svc_id` =  ?", array($db_id));
    }
  }

  echo("\n");

  /// End Netscaler
}

// EOF
