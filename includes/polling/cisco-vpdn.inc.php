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

#CISCO-VPDN-MGMT-MIB::cvpdnTunnelTotal.0 = Gauge32: 0 tunnels
#CISCO-VPDN-MGMT-MIB::cvpdnSessionTotal.0 = Gauge32: 0 users
#CISCO-VPDN-MGMT-MIB::cvpdnDeniedUsersTotal.0 = Counter32: 0 attempts
#CISCO-VPDN-MGMT-MIB::cvpdnSystemTunnelTotal.l2tp = Gauge32: 437 tunnels
#CISCO-VPDN-MGMT-MIB::cvpdnSystemSessionTotal.l2tp = Gauge32: 1029 sessions
#CISCO-VPDN-MGMT-MIB::cvpdnSystemDeniedUsersTotal.l2tp = Counter32: 0 attempts
#CISCO-VPDN-MGMT-MIB::cvpdnSystemClearSessions.0 = INTEGER: none(1)

// FIXME. Candidate for migrate to graphs module with table_collect()
if (is_device_mib($device, 'CISCO-VPDN-MGMT-MIB'))
{
  $data = snmpwalk_cache_oid($device, "cvpdnSystemEntry", NULL, "CISCO-VPDN-MGMT-MIB", mib_dirs('cisco'));

  foreach ($data as $type => $vpdn)
  {
    $rrd_filename = "vpdn-".$type.".rrd";

    if (is_file($rrd_filename) || $vpdn['cvpdnSystemTunnelTotal'] || $vpdn['cvpdnSystemSessionTotal'])
    {
      rrdtool_create($device, $rrd_filename, " DS:tunnels:GAUGE:600:0:U DS:sessions:GAUGE:600:0:U DS:denied:COUNTER:600:0:100000" );

      rrdtool_update($device, $rrd_filename, array($vpdn['cvpdnSystemTunnelTotal'], $vpdn['cvpdnSystemSessionTotal'], $vpdn['cvpdnSystemDeniedUsersTotal']));

      $graphs['vpdn_sessions_'.$type]   = TRUE;
      $graphs['vpdn_tunnels_'.$type]   = TRUE;

      echo(" Cisco VPDN ($type) ");
    }
  }
  unset($data, $vpdn, $type, $rrd_filename);
}

// EOF
