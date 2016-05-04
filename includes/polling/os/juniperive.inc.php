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

$version = trim(snmp_get($device, "productVersion.0", "-OQv", "JUNIPER-IVE-MIB"),'"');
$hardware = "Juniper " . trim(snmp_get($device, "productName.0", "-OQv", "JUNIPER-IVE-MIB"),'"');
$hostname = snmp_get($device, "sysName.0","-OQv");

$rrd_filename = "juniperive_users.rrd";
$clusterusers = snmp_get($device, "clusterConcurrentUsers.0", "-OQv", "JUNIPER-IVE-MIB");
$iveusers = snmp_get($device, "iveConcurrentUsers.0", "-OQv", "JUNIPER-IVE-MIB");

if (!is_null($clusterusers) and !is_null($iveusers))
{
  rrdtool_create($device, $rrd_filename, "\
        DS:clusterusers:GAUGE:600:0:3000000 \
        DS:iveusers:GAUGE:600:0:3000000 ");
  rrdtool_update($device, $rrd_filename, "N:$clusterusers:$iveusers");
  $graphs['juniperive_users'] = TRUE;
}

$rrd_filename = "juniperive_meetings.rrd";
$meetingusers = snmp_get($device, "meetingUserCount.0", "-OQv", "JUNIPER-IVE-MIB");
$meetings = snmp_get($device, "meetingCount.0", "-OQv", "JUNIPER-IVE-MIB");

if (is_numeric($meetingusers) and is_numeric($meetings))
{
  rrdtool_create($device, $rrd_filename, "\
        DS:meetingusers:GAUGE:600:0:3000000 \
        DS:meetings:GAUGE:600:0:3000000 ");
  rrdtool_update($device, $rrd_filename, "N:$meetingusers:$meetings");
  $graphs['juniperive_meetings'] = TRUE;
}

$rrd_filename = "juniperive_connections.rrd";
$webusers = snmp_get($device, "signedInWebUsers.0", "-OQv", "JUNIPER-IVE-MIB");
$mailusers = snmp_get($device, "signedInMailUsers.0", "-OQv", "JUNIPER-IVE-MIB");

if (!is_null($webusers) and !is_null($mailusers))
{
  rrdtool_create($device, $rrd_filename, "\
        DS:webusers:GAUGE:600:0:3000000 \
        DS:mailusers:GAUGE:600:0:3000000 ");
  rrdtool_update($device, $rrd_filename, "N:$webusers:$mailusers");
  $graphs['juniperive_connections'] = TRUE;
}

$rrd_filename = "juniperive_storage.rrd";
$diskpercent = snmp_get($device, "diskFullPercent.0", "-OQv", "JUNIPER-IVE-MIB");
$logpercent = snmp_get($device, "logFullPercent.0", "-OQv", "JUNIPER-IVE-MIB");

if (!is_null($diskpercent) and !is_null($logpercent))
{
  rrdtool_create($device, $rrd_filename, "\
        DS:diskpercent:GAUGE:600:0:3000000 \
        DS:logpercent:GAUGE:600:0:3000000 ");
  rrdtool_update($device, $rrd_filename, "N:$diskpercent:$logpercent");
  $graphs['juniperive_storage'] = TRUE;
}

// EOF
