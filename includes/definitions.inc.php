<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/////////////////////////////////////////////////////////
//  NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE   //
/////////////////////////////////////////////////////////
//               YES, THAT MEANS YOU                   //
/////////////////////////////////////////////////////////

// Include OS definitions
include($config['install_dir'].'/includes/definitions/os.inc.php');

// Include Graph Type definitions
include($config['install_dir'].'/includes/definitions/graphtypes.inc.php');

// VMWare guestid => description definitions
include($config['install_dir'].'/includes/definitions/vmware_guestid.inc.php');

// Apps system definitions
include($config['install_dir'].'/includes/definitions/apps.inc.php');

// Entity type definitions
include($config['install_dir'].'/includes/definitions/entities.inc.php');

// Sensors definitions
include($config['install_dir'].'/includes/definitions/sensors.inc.php');

// Alert Graphs
## FIXME - this is ugly

$config['alert_graphs']['port']['ifInOctets_rate']       = array('type' => 'port_bits', 'id' => '@port_id');
$config['alert_graphs']['port']['ifOutOctets_rate']      = array('type' => 'port_bits', 'id' => '@port_id');
$config['alert_graphs']['port']['ifInOctets_perc']       = array('type' => 'port_percent', 'id' => '@port_id');
$config['alert_graphs']['port']['ifOutOctets_perc']      = array('type' => 'port_percent', 'id' => '@port_id');
$config['alert_graphs']['mempool']['mempool_perc']       = array('type' => 'mempool_usage', 'id' => '@mempool_id');
$config['alert_graphs']['sensor']['sensor_value']        = array('type' => 'sensor_graph', 'id' => '@sensor_id');
$config['alert_graphs']['processor']['processor_usage']  = array('type' => 'processor_usage', 'id' => '@processor_id');

// Device Types

$i = 0;
$config['device_types'][$i]['text'] = 'Servers';
$config['device_types'][$i]['type'] = 'server';
$config['device_types'][$i]['icon'] = 'oicon-server';

$i++;
$config['device_types'][$i]['text'] = 'Workstations';
$config['device_types'][$i]['type'] = 'workstation';
$config['device_types'][$i]['icon'] = 'oicon-computer';

$i++;
$config['device_types'][$i]['text'] = 'Network';
$config['device_types'][$i]['type'] = 'network';
$config['device_types'][$i]['icon'] = 'oicon-network-hub';

$i++;
$config['device_types'][$i]['text'] = 'Wireless';
$config['device_types'][$i]['type'] = 'wireless';
$config['device_types'][$i]['icon'] = 'oicon-wi-fi-zone';

$i++;
$config['device_types'][$i]['text'] = 'Firewalls';
$config['device_types'][$i]['type'] = 'firewall';
$config['device_types'][$i]['icon'] = 'oicon-wall-brick';

$i++;
$config['device_types'][$i]['text'] = 'Power';
$config['device_types'][$i]['type'] = 'power';
$config['device_types'][$i]['icon'] = 'oicon-plug';

$i++;
$config['device_types'][$i]['text'] = 'Environment';
$config['device_types'][$i]['type'] = 'environment';
$config['device_types'][$i]['icon'] = 'oicon-water';

$i++;
$config['device_types'][$i]['text'] = 'Load Balancers';
$config['device_types'][$i]['type'] = 'loadbalancer';
$config['device_types'][$i]['icon'] = 'oicon-arrow-split';

$i++;
$config['device_types'][$i]['text'] = 'Video';
$config['device_types'][$i]['type'] = 'video';
$config['device_types'][$i]['icon'] = 'oicon-surveillance-camera';

$i++;
$config['device_types'][$i]['text'] = 'VoIP';
$config['device_types'][$i]['type'] = 'voip';
$config['device_types'][$i]['icon'] = 'oicon-telephone';

$i++;
$config['device_types'][$i]['text'] = 'Storage';
$config['device_types'][$i]['type'] = 'storage';
$config['device_types'][$i]['icon'] = 'oicon-database';

if (isset($config['enable_printers']) && $config['enable_printers'])
{
  $i++;
  $config['device_types'][$i]['text'] = 'Printers';
  $config['device_types'][$i]['type'] = 'printer';
  $config['device_types'][$i]['icon'] = 'oicon-printer-color';
}

// Syslog colour and name translation

$config['syslog']['priorities']['0'] = array('name' => 'emergency',     'color' => '#D94640');
$config['syslog']['priorities']['1'] = array('name' => 'alert',         'color' => '#D94640');
$config['syslog']['priorities']['2'] = array('name' => 'critical',      'color' => '#D94640');
$config['syslog']['priorities']['3'] = array('name' => 'error',         'color' => '#E88126');
$config['syslog']['priorities']['4'] = array('name' => 'warning',       'color' => '#F2CA3F');
$config['syslog']['priorities']['5'] = array('name' => 'notification',  'color' => '#107373');
$config['syslog']['priorities']['6'] = array('name' => 'informational', 'color' => '#499CA6');
$config['syslog']['priorities']['7'] = array('name' => 'debugging',     'color' => '#5AA637');

for ($i = 8; $i < 16; $i++)
{
  $config['syslog']['priorities'][$i] = array('name' => 'other',        'color' => '#D2D8F9');
}

// This is used to provide pretty rewrites for lowercase things we drag out of the db and use in URLs

$config['nicecase'] = array(
    "bgp_peer" => "BGP Peer",
    "cbgp_peer" => "BGP Peer (AFI/SAFI)",
    "netscaler_vsvr" => "Netscaler vServer",
    "netscaler_svc" => "Netscaler Service",
    "mempool" => "Memory",
    "ipsec_tunnels" => "IPSec Tunnels",
    "vrf" => "VRFs",
    "isis" => "IS-IS",
    "cef" => "CEF",
    "eigrp" => "EIGRP",
    "ospf" => "OSPF",
    "bgp" => "BGP",
    "ases" => "ASes",
    "vpns" => "VPNs",
    "dbm" => "dBm",
    "snr" => "Signal-to-Noise Ratio",
    "mysql" => "MySQL",
    "powerdns" => "PowerDNS",
    "bind" => "BIND",
    "ntpd" => "NTPd",
    "powerdns-recursor" => "PowerDNS Recursor",
    "freeradius" => "FreeRADIUS",
    "postfix_mailgraph" => "Postfix Mailgraph",
    "ge" => "Greater or equal",
    "le" => "Less or equal",
    "notequals" => "Doesn't equal",
    "notmatch" => "Doesn't match",
    "diskio" => "Disk I/O",
    "ipmi" => "IPMI",
    "snmp" => "SNMP",
    "mssql" => "SQL Server",
    "apower" => "Apparent power",
    "proxysg" => "Proxy SG",
    "http" => "HTTP",
    "tcp" => "TCP",
    "udp" => "UDP",
    "ssl" => "SSL");

// Routing types

$config['routing_types']['isis']      = array('text' => 'ISIS');
$config['routing_types']['ospf']      = array('text' => 'OSPF');
$config['routing_types']['cef']       = array('text' => 'CEF');
$config['routing_types']['bgp']       = array('text' => 'BGP');
$config['routing_types']['eigrp']     = array('text' => 'EIGRP');
$config['routing_types']['vrf']       = array('text' => 'VRFs');

// IPMI user levels (used in GUI, first entry = default if unset)

$config['ipmi']['userlevels']['USER']          = array('text' => 'User');
$config['ipmi']['userlevels']['OPERATOR']      = array('text' => 'Operator');
$config['ipmi']['userlevels']['ADMINISTRATOR'] = array('text' => 'Administrator');
$config['ipmi']['userlevels']['CALLBACK']      = array('text' => 'Callback');

// IPMI interfaces (used in GUI, first entry = default if unset)

$config['ipmi']['interfaces']['lan']     = array('text' => 'IPMI v1.5 LAN Interface');
$config['ipmi']['interfaces']['lanplus'] = array('text' => 'IPMI v2.0 RMCP+ LAN Interface');
$config['ipmi']['interfaces']['imb']     = array('text' => 'Intel IMB Interface');
$config['ipmi']['interfaces']['open']    = array('text' => 'Linux OpenIPMI Interface');

// Toner colour mapping
$config['toner']['cyan']    = array('cyan');
$config['toner']['magenta'] = array('magenta');
$config['toner']['yellow']  = array('yellow', 'giallo', 'gul');
$config['toner']['black']   = array('black', 'preto', 'nero');

// Nicer labels for the SLA types
$config['sla_type_labels']['echo'] = 'ICMP ping';
$config['sla_type_labels']['pathEcho'] = 'Path ICMP ping';
$config['sla_type_labels']['fileIO'] = 'File I/O';
$config['sla_type_labels']['script'] = 'Script';
$config['sla_type_labels']['udpEcho'] = 'UDP ping';
$config['sla_type_labels']['tcpConnect'] = 'TCP connect';
$config['sla_type_labels']['http'] = 'HTTP';
$config['sla_type_labels']['dns'] = 'DNS';
$config['sla_type_labels']['jitter'] = 'Jitter';
$config['sla_type_labels']['dlsw'] = 'DLSW';
$config['sla_type_labels']['dhcp'] = 'DHCP';
$config['sla_type_labels']['ftp'] = 'FTP';
$config['sla_type_labels']['voip'] = 'VoIP';
$config['sla_type_labels']['rtp'] = 'RTP';
$config['sla_type_labels']['lspGroup'] = 'LSP group';
$config['sla_type_labels']['icmpjitter'] = 'ICMP jitter';
$config['sla_type_labels']['lspPing'] = 'LSP ping';
$config['sla_type_labels']['lspTrace'] = 'LSP trace';
$config['sla_type_labels']['ethernetPing'] = 'Ethernet ping';
$config['sla_type_labels']['ethernetJitter'] = 'Ethernet jitter';
$config['sla_type_labels']['lspPingPseudowire'] = 'LSP Pseudowire ping';

// RANCID OS map (for config generation script)
$config['rancid']['os_map']['arista'] = 'arista';
$config['rancid']['os_map']['avocent'] = 'avocent';
$config['rancid']['os_map']['f5'] = 'f5';
$config['rancid']['os_map']['fortigate'] = 'fortigate';
$config['rancid']['os_map']['ftos'] = 'force10';
$config['rancid']['os_map']['ios'] = 'cisco';
$config['rancid']['os_map']['iosxe'] = 'cisco';
$config['rancid']['os_map']['iosxr'] = 'cisco-xr';
$config['rancid']['os_map']['ironware'] = 'foundry';
$config['rancid']['os_map']['hp'] = 'hp';
$config['rancid']['os_map']['junos'] = 'juniper';
$config['rancid']['os_map']['nxos'] = 'cisco-nx';
$config['rancid']['os_map']['routeros'] = 'mikrotik';
$config['rancid']['os_map']['screenos'] = 'netscreen';
$config['rancid']['os_map']['pfsense'] = 'pfsense';
$config['rancid']['os_map']['asa'] = 'cisco';
# Enable these (in config.php) if you added the powerconnect addon to your RANCID install
#$config['rancid']['os_map']['powerconnect-fastpath'] = 'dell';
#$config['rancid']['os_map']['powerconnect-radlan'] = 'dell';

//////////////////////////////////////////////////////////////////////////
// No changes below this line // (no changes above it either, remember? //
//////////////////////////////////////////////////////////////////////////

// Include from PEAR
set_include_path($config['install_dir'] . "/includes/pear" . PATH_SEPARATOR . get_include_path());

include($config['install_dir'] . "/includes/pear/Net/IPv4.php");
include($config['install_dir'] . "/includes/pear/Net/IPv6.php");
include($config['install_dir'] . "/includes/pear/Net/MAC.php");

include($config['install_dir'].'/includes/definitions/version.inc.php');

if (isset($config['rrdgraph_def_text']))
{
  $config['rrdgraph_def_text'] = str_replace("  ", " ", $config['rrdgraph_def_text']);
  $config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));
}

// Set default paths.
$config['install_dir'] = rtrim($config['install_dir'], ' /');
if (!isset($config['html_dir'])) { $config['html_dir'] = $config['install_dir'] . '/html'; }
else                             { $config['html_dir'] = rtrim($config['html_dir'], ' /'); }
if (!isset($config['rrd_dir']))  { $config['rrd_dir']  = $config['install_dir'] . '/rrd'; }
else                             { $config['rrd_dir']  = rtrim($config['rrd_dir'], ' /'); }
if (!isset($config['log_dir']))  { $config['log_dir']  = $config['install_dir'] . '/logs'; }
else                             { $config['log_dir']  = rtrim($config['log_dir'], ' /'); }
if (!isset($config['log_file'])) { $config['log_file'] = $config['log_dir'] . '/observium.log'; } // FIXME should not be absolute path, look for where it is used
if (!isset($config['temp_dir'])) { $config['temp_dir'] = '/tmp'; }
else                             { $config['temp_dir'] = rtrim($config['temp_dir'], ' /'); }
if (!isset($config['mib_dir']))  { $config['mib_dir']  = $config['install_dir'] . '/mibs'; }
else                             { $config['mib_dir']  = rtrim($config['mib_dir'], ' /'); }

// Try to create log directory if it doesn't exist
if (!is_dir($config['log_dir'])) { mkdir($config['log_dir']); } // CLEANME remove in r6000

// Old variable backwards compatibility
if (isset($config['rancid_configs']) && !is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }
if (isset($config['auth_ldap_group']) && !is_array($config['auth_ldap_group'])) { $config['auth_ldap_group'] = array($config['auth_ldap_group']); }

// Database currently stores v6 networks non-compressed, check for any compressed subnet and expand them
foreach ($config['ignore_common_subnet'] as $index => $content)
{
  if (strstr($content,':') !== FALSE) { $config['ignore_common_subnet'][$index] = Net_IPv6::uncompress($content); }
}

// Disable nonexistant features in CE, do not try to turn on, it will not give effect
if (OBSERVIUM_EDITION == 'community')
{
  $config['enable_billing'] = 0;
  $config['poller-wrapper']['alerter'] = FALSE;
}

// If we're on SSL, let's properly detect it
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function is_ssl()
{
  if (isset($_SERVER['HTTPS']))
  {
    if ('on' == strtolower($_SERVER['HTTPS'])) { return TRUE; }
    if ('1' == $_SERVER['HTTPS']) { return TRUE; }
  }
  else if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']))
  {
    return TRUE;
  }
  else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
  {
    return TRUE;
  }
  return FALSE;
}

if (!isset($config['own_hostname']))
{
  /// FIXME. Need use get_hostname() in definitions but for this requires fix all MOVEME in common
  $config['own_hostname'] = "localhost";
}

if (!isset($config['base_url']))
{
  if (isset($_SERVER["SERVER_NAME"]) && isset($_SERVER["SERVER_PORT"]))
  {
    if (strpos($_SERVER["SERVER_NAME"] , ":"))
    {
      // Literal IPv6
      $config['base_url']  = "http://[" . $_SERVER["SERVER_NAME"] ."]" . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
    } else {
      $config['base_url']  = "http://" . $_SERVER["SERVER_NAME"] . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
    }
  }
  //} else {
  //  // Try to detect base_url in cli based on hostname
  //  /// FIXME. Here require get_localhost(), but this function loaded after definitions
  //  //$config['base_url'] = "http://" . get_localhost() . "/";
  //}
} else {
  // Add / to base_url if not there
  if (substr($config['base_url'], -1) != '/') { $config['base_url'] .= '/'; }
}

if (is_ssl())
{
  $config['base_url'] = preg_replace('/^http:/','https:', $config['base_url']);
}

if (!isset($config['web_url']))
{
  $config['web_url'] = isset($config['base_url']) ? $config['base_url'] : 'http://localhost:80';
}
if (substr($config['web_url'], -1) != '/') { $config['web_url'] .= '/'; }

// Connect to database
$observium_link = mysql_connect($config['db_host'], $config['db_user'], $config['db_pass']);
if (!$observium_link)
{
  include_once("common.inc.php");

  print_error("MySQL Error: " . mysql_error());
  die;
}
$observium_db = mysql_select_db($config['db_name'], $observium_link);

// Connect to statsd

#if($config['statsd']['enable'])
#{
#  $log = new \StatsD\Client($config['statsd']['host'].':'.$config['statsd']['port']);
#}

// Set some times needed by loads of scripts (it's dynamic, so we do it here!)
$config['time']['now']        = time();
$config['time']['fourhour']   = $config['time']['now'] - 14400;    //time() - (4 * 60 * 60);
$config['time']['sixhour']    = $config['time']['now'] - 21600;    //time() - (6 * 60 * 60);
$config['time']['twelvehour'] = $config['time']['now'] - 43200;    //time() - (12 * 60 * 60);
$config['time']['day']        = $config['time']['now'] - 86400;    //time() - (24 * 60 * 60);
$config['time']['twoday']     = $config['time']['now'] - 172800;   //time() - (2 * 24 * 60 * 60);
$config['time']['week']       = $config['time']['now'] - 604800;   //time() - (7 * 24 * 60 * 60);
$config['time']['twoweek']    = $config['time']['now'] - 1209600;  //time() - (2 * 7 * 24 * 60 * 60);
$config['time']['month']      = $config['time']['now'] - 2678400;  //time() - (31 * 24 * 60 * 60);
$config['time']['twomonth']   = $config['time']['now'] - 5356800;  //time() - (2 * 31 * 24 * 60 * 60);
$config['time']['threemonth'] = $config['time']['now'] - 8035200;  //time() - (3 * 31 * 24 * 60 * 60);
$config['time']['sixmonth']   = $config['time']['now'] - 16070400; //time() - (6 * 31 * 24 * 60 * 60);
$config['time']['year']       = $config['time']['now'] - 31536000; //time() - (365 * 24 * 60 * 60);
$config['time']['twoyear']    = $config['time']['now'] - 63072000; //time() - (2 * 365 * 24 * 60 * 60);

// Tables to clean up when deleting a device.
// FIXME. Need simple way for fetch list tables with column 'device_id', like 'SHOW TABLES'
$config['device_tables'] = array('accesspoints', 'alerts', 'alert_log', 'alert_table', 'applications',
                                 'bgpPeers', 'bgpPeers_cbgp', 'cef_prefix', 'cef_switching', 'devices_attribs',
                                 'devices_perftimes', 'device_graphs', 'eigrp_ports', 'entPhysical', 'eventlog',
                                 'hrDevice', 'ipsec_tunnels', 'loadbalancer_rservers', 'loadbalancer_vservers',
                                 'mempools', 'munin_plugins', 'netscaler_services', 'netscaler_services_vservers',
                                 'netscaler_vservers', 'ospf_areas', 'ospf_instances', 'ospf_nbrs', 'ospf_ports',
                                 'packages', 'ports', 'ports_stack', 'ports_vlans', 'processors', 'pseudowires',
                                 'sensors', 'services', 'slas', 'storage', 'syslog', 'toner', 'ucd_diskio', 'vlans',
                                 'vlans_fdb', 'vminfo', 'vrfs', 'wifi_accesspoints', 'wifi_sessions',
                                 'entity_permissions', 'group_table', 'devices_mibs', 'devices');

// End of includes/definitions.inc.php
