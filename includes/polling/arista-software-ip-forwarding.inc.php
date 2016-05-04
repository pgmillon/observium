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

if ($device['os'] == "arista_eos")
{
  echo("ARISTA-SW-IP-FORWARDING\n");

  $data = snmpwalk_cache_oid($device, "aristaSwFwdIpStatsTable", array(), "ARISTA-SW-IP-FORWARDING-MIB", mib_dirs('arista'));
  $oids = array ('HCInReceives', 'InHdrErrors', 'InNoRoutes', 'InAddrErrors',
                 'InUnknownProtos', 'InTruncatedPkts',
                 'HCInForwDatagrams',
                 'ReasmReqds', 'ReasmOKs', 'ReasmFails',
                 'OutNoRoutes', 'HCOutForwDatagrams',
                 'OutDiscards',
                 'OutFragReqds', 'OutFragOKs', 'OutFragFails', 'OutFragCreates',
                 'HCOutTransmits' );

  $rrdfile = "arista-netstats-sw-ip.rrd";
  $rrdfile6 = "arista-netstats-sw-ip6.rrd";

  foreach ($oids as $oid)
  {
    $oid_ds = str_replace("HC", "", $oid);
    $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
  }

  $have6 = isset( $data['ipv6'] );
  $rrdupdate = "N";
  $rrdupdate6 = "N";

  foreach ($oids as $oid)
  {
    $rrdupdate .= ":" .$data[ 'ipv4' ][ 'aristaSwFwdIpStats' . $oid ];
    if ($have6)
    {
      $rrdupdate6 .= ":" .$data[ 'ipv6' ][ 'aristaSwFwdIpStats' . $oid ];
    }
  }

  rrdtool_create($device,$rrdfile, $rrd_create);
  rrdtool_update($device, $rrdfile, $rrdupdate);

  if ($have6)
  {
    rrdtool_create($device, $rrdfile6, $rrd_create);
    rrdtool_update($device, $rrdfile6, $rrdupdate6);
  }

  unset($data, $oid, $oids, $oid_ds, $rrdfile, $rrdupate, $rrd_create);

  $graphs['netstat_arista_sw_ip'] = TRUE;
  $graphs['netstat_arista_sw_ip_frag'] = TRUE;
  if ($have6)
  {
    $graphs['netstat_arista_sw_ip6'] = TRUE;
    $graphs['netstat_arista_sw_ip6_frag'] = TRUE;
  }
}

// EOF
