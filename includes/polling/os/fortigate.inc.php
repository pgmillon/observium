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

$fnSysVersion = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysVersion.0", "-Ovq", NULL, mib_dirs('fortinet'));
$serial       = snmp_get($device, "FORTINET-CORE-MIB::fnSysSerial.0", "-Ovq", NULL, mib_dirs('fortinet'));

$version = preg_replace("/(.+),(.+),(.+)/", "\\1||\\2||\\3", $fnSysVersion);
list($version,$features) = explode("||", $version);

$hardware = rewrite_fortinet_hardware($poll_device['sysObjectID']);

$sessrrd  = "fortigate_sessions.rrd";
$sessions = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgSysSesCount.0", "-Ovq", NULL, mib_dirs('fortinet'));

if (is_numeric($sessions))
{
  rrdtool_create($device, $sessrrd,"  DS:sessions:GAUGE:600:0:3000000 ");
  print_cli_data ("Firewall Sessions", $sessions);
  rrdtool_update($device, $sessrrd,"N:".$sessions);
  $graphs['fortigate_sessions'] = TRUE;
}

// EOF
