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

//  Polling of AP and radios status for Juniper Wireless (ex Trapeze)
//
//  TRAPEZE-NETWORKS-AP-STATUS-MIB

echo(" TRAPEZE-NETWORKS-AP-STATUS-MIB ");

// getting APs and radios

$radios_snmp = snmpwalk_cache_twopart_oid($device, "trpzApStatRadioOpStatisticsTable", $radios_snmp, "TRAPEZE-NETWORKS-AP-STATUS-MIB", mib_dirs('trapeze'));
if (OBS_DEBUG > 1 && count($radios_snmp)) { print_vars($radios_snmp); }

// OIDs to graph
$oids_counter = array(
  'TxUniPkt',
  'TxUniOctet',
  'TxMultiPkt',
  'TxMultiOctet',
  'RxPkt',
  'RxOctet',
  'UndcrptPkt',
  'UndcrptOctet',
  'PhyErr',
  'ResetCount',
  'AutoTuneChannelChangeCount',
  'TxRetriesCount',
  'ClientAssociations',
  'ClientFailedAssociations',
  'ClientReAssociations',
  'SignalingPkt',
  'ReTransmitOctet',
  'RefusedConnectionCount',
  'RxDataPkt',
  'RxAuthPkt',
  'RxAssocPkt',
  'TxDataPkt',
  'TxAuthRespPkt',
  'TxAssocRespPkt');

$oids_gauge = array(
  'UserSessions',
  'NoiseFloor');

// Goes through the SNMP radio data
foreach ($radios_snmp as $ap_serial => $ap_radios)
{
  foreach ($ap_radios as $radio_number => $radio)
  {
    $rrdupdate = "N";
    $rrd_create = "";
    if ($radio_number == "radio-1")      { $radio_number = 1; } // FIXME just get number from radio-X ?
    else if ($radio_number == "radio-2") { $radio_number = 2; }
    $rrd_file = "wifi-radio-". $ap_serial . '-' . $radio_number.".rrd";

    foreach ($oids_gauge as $oid)
    {
      $oid_ds = truncate($oid, 19, '');
      $rrd_create .= " DS:$oid_ds:GAUGE:600:U:125000000000";

      if (is_numeric($radio['trpzApStatRadioOpStats'.$oid]))
      {
        $value = $radio['trpzApStatRadioOpStats'.$oid];
      } else {
        $value = "0";
      }
      $rrdupdate .= ":$value";
    }
    foreach ($oids_counter as $oid)
    {
      $oid_ds = truncate($oid, 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:0:125000000000";

      if (is_numeric($radio['trpzApStatRadioOpStats'.$oid]))
      {
        $value = $radio['trpzApStatRadioOpStats'.$oid];
      } else {
        $value = "0";
      }
      $rrdupdate .= ":$value";
    }
    rrdtool_create($device, $rrd_file, $rrd_create);
    rrdtool_update($device, $rrd_file, $rrdupdate);
  }
}

unset($oids, $oid, $radio);

// EOF
