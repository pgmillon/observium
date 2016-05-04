<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$fnSysVersion = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysVersion.0", "-Ovq");
$serial       = snmp_get($device, "FORTINET-FORTIGATE-MIB::fnSysSerial.0", "-Ovq");

$version = preg_replace("/(.+),(.+),(.+)/", "\\1||\\2||\\3", $fnSysVersion);
list($version,$features) = explode("||", $version);

if (isset($rewrite_fortinet_hardware[$poll_device['sysObjectID']]))
{
  $hardware = $rewrite_fortinet_hardware[$poll_device['sysObjectID']];
}

$sessrrd  = "fortigate_sessions.rrd";
$sessions = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysSesCount.0", "-Ovq");

if (is_numeric($sessions))
{
  rrdtool_create($device, $sessrrd,"  DS:sessions:GAUGE:600:0:3000000 ");
  print "Sessions: $sessions\n";
  rrdtool_update($device, $sessrrd,"N:".$sessions);
  $graphs['fortigate_sessions'] = TRUE;
}



// EOF
