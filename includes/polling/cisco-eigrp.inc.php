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

// Interface table - CEigrpInterfaceTable
// cEigrpPeerCount.0.1.12 = 1
// cEigrpXmitReliableQ.0.1.12 = 0
// cEigrpXmitUnreliableQ.0.1.12 = 0
// cEigrpMeanSrtt.0.1.12 = 34
// cEigrpPacingReliable.0.1.12 = 4
// cEigrpPacingUnreliable.0.1.12 = 0
// cEigrpMFlowTimer.0.1.12 = 144
// cEigrpPendingRoutes.0.1.12 = 0
// cEigrpHelloInterval.0.1.12 = 5
// cEigrpXmitNextSerial.0.1.12 = 0
// cEigrpUMcasts.0.1.12 = 0
// cEigrpRMcasts.0.1.12 = 123
// cEigrpUUcasts.0.1.12 = 136
// cEigrpRUcasts.0.1.12 = 34
// cEigrpMcastExcepts.0.1.12 = 5
// cEigrpCRpkts.0.1.12 = 1
// cEigrpAcksSuppressed.0.1.12 = 1
// cEigrpRetransSent.0.1.12 = 22
// cEigrpOOSrvcd.0.1.12 = 0
// cEigrpAuthMode.0.1.12 = none
// cEigrpAuthKeyChain.0.1.12 =

// Only run this on Cisco kit.

if($device['os_group'] == "cisco")
{

$port_db_q = dbFetchRows("SELECT * FROM `eigrp_ports` WHERE `device_id` = ?", array($device['device_id']));

$port_db = array();
foreach ($port_db_q AS $db_port)
{
  $port_db[$db_port['eigrp_vpn']."-".$db_port['eigrp_as']."-".$db_port['eigrp_ifIndex']] = $db_port;
}

print_vars($port_db);

$ports_poll = snmpwalk_cache_oid($device, "CEigrpInterfaceEntry", array(), "CISCO-EIGRP-MIB", mib_dirs(array("cisco")));

foreach ($ports_poll AS $id => $eigrp_port)
{
  list($vpn, $as, $ifIndex) = explode(".", $id);
  echo("$vpn $as $ifIndex".PHP_EOL);

  $port = get_port_by_index_cache($device['device_id'], $ifIndex);

  if (is_array($port_db[$vpn."-".$as."-".$ifIndex]))
  {
    $eigrp_update = NULL;

    if ($port['port_id'] != $port_db[$vpn."-".$as."-".$ifIndex]['port_id']) { $eigrp_update['port_id'] = $port['port_id']; }
    if ($eigrp_port['cEigrpAuthMode'] != $port_db[$vpn."-".$as."-".$ifIndex]['eigrp_authmode']) { $eigrp_update['eigrp_authmode'] = $eigrp_port['cEigrpAuthMode']; }
    if ($eigrp_port['cEigrpMeanSrtt'] != $port_db[$vpn."-".$as."-".$ifIndex]['eigrp_MeanSrtt']) { $eigrp_update['eigrp_MeanSrtt'] = $eigrp_port['cEigrpMeanSrtt']; }

    if (is_array($eigrp_update)) { dbUpdate($eigrp_update, 'eigrp_ports', '`eigrp_port_id` = ?', array($port_db[$vpn."-".$as."-".$ifIndex]['eigrp_port_id'])); }
    unset ($eigrp_update);

  } else {
    dbInsert(array('eigrp_vpn' => $vpn, 'eigrp_as' => $as, 'eigrp_ifIndex' => $ifIndex, 'port_id' => $port['port_id'], 'device_id' => $device['device_id'], 'eigrp_peer_count' => $eigrp_port['cEigrpPeerCount']), 'eigrp_ports');
    echo("+");
  }

  // Write per-interface EIGRP statistics

  $rrd_filename   = $host_rrd . "/eigrp_port-".$vpn."-".$as."-".$ifIndex.".rrd";

  rrdtool_create($device, $rrd_filename, " \
     DS:MeanSrtt:GAUGE:600:0:10000 \
     DS:UMcasts:COUNTER:600:0:10000000000 \
     DS:RMcasts:COUNTER:600:0:10000000000 \
     DS:UUcasts:COUNTER:600:0:10000000000 \
     DS:RUcasts:COUNTER:600:0:10000000000 \
     DS:McastExcepts:COUNTER:600:0:10000000000 \
     DS:CRpkts:COUNTER:600:0:10000000000 \
     DS:AcksSuppressed:COUNTER:600:0:10000000000 \
     DS:RetransSent:COUNTER:600:0:10000000000 \
     DS:OOSrvcd:COUNTER:600:0:10000000000 \
     ");

  foreach (array("MeanSrtt", "UMcasts", "RMcasts", "UUcasts", "RUcasts", "McastExcepts", "CRpkts", "AcksSuppressed", "RetransSent", "OOSrvcd") as $oid) { $eigrp_update[] = $eigrp_port['cEigrp'.$oid]; }

  rrdtool_update($device, $rrd_filename, $eigrp_update);

  echo PHP_EOL;

  unset ($eigrp_update);

 }

} // End if CISCO

// EOF
