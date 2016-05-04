<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/////////////////////////////////////////////////////////
//  NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE   //
/////////////////////////////////////////////////////////
//               YES, THAT MEANS YOU                   //
/////////////////////////////////////////////////////////

// Always set locale to EN, because we use parsing strings
setlocale(LC_ALL, 'C');
putenv('LC_ALL=C');
// Use default charset UTF-8
ini_set('default_charset', 'UTF-8');

// Flags (mostly used in snmp and network functions)
// Bits 0-3 common flags
define('OBS_QUOTES_STRIP',   1); // Strip ALL quotes from string
define('OBS_QUOTES_TRIM',    2); // Trim quotes only from begin/end of string
define('OBS_ESCAPE',         4); // Escape strings or output
#define('OBS_',               8); // Reserved

// Bits 4-7 snmp flags
define('OBS_SNMP_NUMERIC',  16); // Use numeric index
define('OBS_SNMP_CONCAT',   32); // Concatinate multiline snmp variable
define('OBS_SNMP_ENUM',     64); // Don't enumerate SNMP values
define('OBS_SNMP_HEX',     128); // Force HEX output
//define('OBS_SNMP_ALL',         OBS_QUOTES_TRIM | OBS_QUOTES_STRIP | OBS_SNMP_CONCAT); // Set of common snmp options
define('OBS_SNMP_ALL',         OBS_QUOTES_TRIM | OBS_QUOTES_STRIP); // Set of common snmp options
define('OBS_SNMP_ALL_HEX',     OBS_SNMP_ALL | OBS_SNMP_HEX);        // Set of common snmp options forcing HEX output
define('OBS_SNMP_ALL_ENUM',    OBS_SNMP_ALL | OBS_SNMP_ENUM);       // Set of common snmp options without enumerating values
define('OBS_SNMP_ALL_NUMERIC', OBS_SNMP_ALL | OBS_SNMP_NUMERIC);    // Set of common snmp options with numeric indexes

// Bits 8-11 network flags
define('OBS_DNS_A',        256); // Use only IPv4 dns queries
define('OBS_DNS_AAAA',     512); // Use only IPv6 dns queries
define('OBS_DNS_ALL',  OBS_DNS_A | OBS_DNS_AAAA); // Use both IPv4/IPv6 dns queries
define('OBS_PING_SKIP',   1024); // Skip device isPingable checks
#define('OBS_',            2048); // Reserved

// Bits 12-15 reserved

// Bits 16- permissions flags
define('OBS_PERMIT_ACCESS',  65536); // Can access (ie: logon)
define('OBS_PERMIT_READ',   131072); // Can read basic data
define('OBS_PERMIT_SECURE', 262144); // Can read secure data
define('OBS_PERMIT_EDIT',   524288); // Can edit
define('OBS_PERMIT_ALL', OBS_PERMIT_ACCESS | OBS_PERMIT_READ | OBS_PERMIT_SECURE | OBS_PERMIT_EDIT); // Permit all

// Set QUIET
define('OBS_QUIET', isset($options['q']));

// Set DEBUG
if (isset($options['d']))
{
  // CLI
  echo("DEBUG!\n");
  define('OBS_DEBUG', count($options['d'])); // -d == 1, -dd == 2..
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  if (OBS_DEBUG > 1)
  {
    //ini_set('error_reporting', E_ALL ^ E_NOTICE); // FIXME, too many warnings ;)
    ini_set('error_reporting', E_ALL ^ E_NOTICE ^ E_WARNING);
  } else {
    ini_set('error_reporting', E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR); // Only various errors
  }
}
else if ((isset($_SERVER['PATH_INFO']) && strpos($_SERVER['PATH_INFO'], 'debug')) ||
         (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'debug')) ||
         (isset($_REQUEST['debug']) && $_REQUEST['debug']))
{
  // WEB

  // Note, for security reasons set OBS_DEBUG constant in WUI moved to auth module
  if (isset($config['web_debug_unprivileged']) && $config['web_debug_unprivileged'])
  {
    define('OBS_DEBUG', 1);
  } else {
    define('OBS_DEBUG_WUI', 1); // Temporary constant, for check in auth module
  }
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL ^ E_NOTICE);
  //$vars['debug'] = 'yes';
} else {
  define('OBS_DEBUG', 0);
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 1);
  //ini_set('error_reporting', 0); // Default
}
$debug = OBS_DEBUG; // DEBUG. Temporary fallback to old variable

// Unit test not used sql connect and does not include includes/sql-config.inc.php
if (defined('__PHPUNIT_PHAR__'))
{
  // Base dir, if it's not set in config
  if (!isset($config['install_dir']))
  {
    $config['install_dir'] = realpath(dirname(__FILE__) . '/..');
  }
  if (!defined('OBS_DB_SKIP'))
  {
    define('OBS_DB_SKIP', TRUE);
  }
  // In phpunit, autoload not work
  set_include_path(dirname(__FILE__) . "/../libs/pear" . PATH_SEPARATOR . get_include_path());
  require("Net/IPv4.php");
  require("Net/IPv6.php");
  require("Console/Color2.php");
  //print_warning("WARNING. In PHP Unit tests can skip MySQL connect. But If you test mysql functions, check your configs.");
} else {
  define('OBS_DB_SKIP', FALSE);
}

// Debug nicer functions
if (OBS_DEBUG || strlen($_SERVER['REMOTE_ADDR']))
{
  // Nicer for print_vars(), for WUI loaded always
  if (!function_exists('rt') && is_file($config['install_dir']."/libs/ref.inc.php"))
  {
    include($config['install_dir']."/libs/ref.inc.php");
  }
}

// Community specific definition
if (is_file($config['install_dir'].'/includes/definitions/definitions.dat'))
{
  //var_dump($config);
  $config_tmp = file_get_contents($config['install_dir'].'/includes/definitions/definitions.dat');
  $config_tmp = gzuncompress($config_tmp);
  $config_tmp = unserialize($config_tmp);
  //var_dump($config_tmp);
  if (is_array($config_tmp) && isset($config_tmp['os'])) // Simple check for passed correct data
  {
    $config = array_merge($config, $config_tmp);
  }
  unset($config_tmp);
}

$definition_files = array('os',           // OS definitions
                          'graphtypes',   // Graph Type definitions
                          'entities',     // Entity type definitions
                          'rewrites',     // Rewriting array definitions
                          'mibs',         // MIB definitions
                          'models',       // Hardware model definitions (leave it after os and rewrites)
                          'sensors',      // Sensors definitions
                          'status',       // Status definitions
                          'geo',          // Geolocation api definitions
                          'vm',           // Virtual Machine definitions
                          'transports',   // Alerting transport definitions
                          'apps',         // Apps system definitions
                          'wui',          // Web UI specific definitions
                          );

foreach ($definition_files as $file)
{
  $file = $config['install_dir'].'/includes/definitions/'.$file.'.inc.php';
  if (is_file($file))
  {
    include($file);
  }
}
unset($definition_files, $file); // Clean


// Alert Graphs
## FIXME - this is ugly
## Merge it in to entities, since that's what it is!

$config['alert_graphs']['port']['ifInOctets_rate']       = array('type' => 'port_bits', 'id' => '@port_id');
$config['alert_graphs']['port']['ifOutOctets_rate']      = array('type' => 'port_bits', 'id' => '@port_id');
$config['alert_graphs']['port']['ifInOctets_perc']       = array('type' => 'port_percent', 'id' => '@port_id');
$config['alert_graphs']['port']['ifOutOctets_perc']      = array('type' => 'port_percent', 'id' => '@port_id');
$config['alert_graphs']['mempool']['mempool_perc']       = array('type' => 'mempool_usage', 'id' => '@mempool_id');
$config['alert_graphs']['sensor']['sensor_value']        = array('type' => 'sensor_graph', 'id' => '@sensor_id');
$config['alert_graphs']['sensor']['sensor_event']        = array('type' => 'sensor_graph', 'id' => '@sensor_id');
$config['alert_graphs']['status']['status_event']        = array('type' => 'status_graph', 'id' => '@status_id');
$config['alert_graphs']['status']['status_state']        = array('type' => 'status_graph', 'id' => '@status_id');

$config['alert_graphs']['processor']['processor_usage']  = array('type' => 'processor_usage', 'id' => '@processor_id');
$config['alert_graphs']['storage']['storage_perc']  = array('type' => 'storage_usage', 'id' => '@storage_id');

// Device Types

$i = (is_array($config['device_types']) ? count($config['device_types']) : 0); // Allow config.php-set device_types to exist
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

$i++;
$config['device_types'][$i]['text'] = 'Management';
$config['device_types'][$i]['type'] = 'management';
$config['device_types'][$i]['icon'] = 'oicon-service-bell'; // FIXME. I really not know what icon better

$i++;
$config['device_types'][$i]['text'] = 'Radio';
$config['device_types'][$i]['type'] = 'radio';
$config['device_types'][$i]['icon'] = 'oicon-transmitter';

if (isset($config['enable_printers']) && $config['enable_printers'])
{
  $i++;
  $config['device_types'][$i]['text'] = 'Printers';
  $config['device_types'][$i]['type'] = 'printer';
  $config['device_types'][$i]['icon'] = 'oicon-printer-color';
}

// SLA colours

$config['sla']['loss_colour'] = array('55FF00', '00FFD5', '00D5FF', '00AAFF', '0080FF', '0055FF', '0000FF', '8000FF', 'D400FF', 'FF00D4', 'FF0080', 'FF0000');
$config['sla']['loss_value'] = array(0, 2, 4, 6, 8, 10, 15, 20, 25, 40, 50, 100);

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

// 'count' is min total errors count, after which autodisable this MIB/oid pair
// 'rate' is min total rate (per poll), after which autodisable this MIB/oid pair
// note, rate not fully correct after server reboot (it will less than really)
$config['snmp']['errorcodes'][0]    = array('reason' => 'OK',
                                            'msg'    => '');
// Non critical
$config['snmp']['errorcodes'][1]    = array('reason' => 'Empty output',     // exitcode = 0, but not have any data
                                            'count'  => 288,                // 288 with rate 1/poll ~ 1 day
                                            'rate'   => 0.9,
                                            'msg'    => '');
$config['snmp']['errorcodes'][2]    = array('reason' => 'Not completed',    // Snmp output return correct data, but stopped by some reason (timeout, network error)
                                            'msg'    => '');
$config['snmp']['errorcodes'][3]    = array('reason' => 'Too long',         // Not empty output, but exitcode = 1 and runtime > 10
                                            'msg'    => '');
$config['snmp']['errorcodes'][900]  = array('reason' => 'isSNMPable',       // Device up/down test, not used for counting
                                            'msg'    => '');
$config['snmp']['errorcodes'][998]  = array('reason' => 'MIB/oid disabled', // MIB or oid disabled
                                            'msg'    => '');
$config['snmp']['errorcodes'][999]  = array('reason' => 'Unknown',          // Some unidentified error
                                            'count'  => 288,                // 288 with rate 1.95/poll ~ 12 hours
                                            'rate'   => 0.9,
                                            'msg'    => '');
// Critical, can autodisable
$config['snmp']['errorcodes'][1000] = array('reason' => 'SNMP error',       // Any critical error in snmp output, which not return useful data
                                            'count'  => 70,                 // errors in every poll run, disable after ~ 6 hours
                                            'rate'   => 0.9,
                                            'msg'    => '');
$config['snmp']['errorcodes'][1001] = array('reason' => 'SNMP auth error',  // Snmp auth errors
                                            'count'  => 25,                 // errors in every poll run, disable after ~ 1.5 hour
                                            'rate'   => 0.9,
                                            'msg'    => '');
$config['snmp']['errorcodes'][1002] = array('reason' => 'Timeout',          // Cmd exit by timeout
                                            'count'  => 25,                 // errors in every poll run, disable after ~ 1.5 hour
                                            'rate'   => 0.9,
                                            'msg'    => '');

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
$config['sla_type_labels']['video'] = 'Video';
$config['sla_type_labels']['y1731Delay'] = 'Y.1731 delay';
$config['sla_type_labels']['y1731Loss'] = 'Y.1731 loss';
$config['sla_type_labels']['mcastJitter'] = 'Multicast jitter';
$config['sla_type_labels']['IcmpEcho'] = 'ICMP ping';
$config['sla_type_labels']['UdpEcho'] = 'UDP ping';
$config['sla_type_labels']['SnmpQuery'] = 'SNMP';
$config['sla_type_labels']['TcpConnectionAttempt'] = 'TCP connect';
$config['sla_type_labels']['IcmpTimeStamp'] = 'ICMP timestamp';
$config['sla_type_labels']['HttpGet'] = 'HTTP';
$config['sla_type_labels']['HttpGetMetadata'] = 'HTTP metadata';
$config['sla_type_labels']['DnsQuery'] = 'DNS';
$config['sla_type_labels']['NtpQuery'] = 'NTP';
$config['sla_type_labels']['UdpTimestamp'] = 'UDP timestamp';

// RANCID OS map (for config generation script)
$config['rancid']['os_map']['arista_eos'] = 'arista';
$config['rancid']['os_map']['asa']        = 'cisco';
$config['rancid']['os_map']['avocent']    = 'avocent';
$config['rancid']['os_map']['f5']         = 'f5';
$config['rancid']['os_map']['fortigate']  = 'fortigate';
$config['rancid']['os_map']['ftos']       = 'force10';
$config['rancid']['os_map']['ios']        = 'cisco';
$config['rancid']['os_map']['iosxe']      = 'cisco';
$config['rancid']['os_map']['iosxr']      = 'cisco-xr';
$config['rancid']['os_map']['ironware']   = 'foundry';
$config['rancid']['os_map']['procurve']   = 'hp';
$config['rancid']['os_map']['pixos']      = 'cisco';
$config['rancid']['os_map']['junos']      = 'juniper';
$config['rancid']['os_map']['nxos'] 	  = 'cisco-nx';
$config['rancid']['os_map']['opengear']   = 'opengear';
$config['rancid']['os_map']['routeros']   = 'mikrotik';
$config['rancid']['os_map']['screenos']   = 'netscreen';
$config['rancid']['os_map']['pfsense']    = 'pfsense';
$config['rancid']['os_map']['netscaler']  = 'netscaler';

# Enable these (in config.php) if you added the powerconnect addon to your RANCID install
#$config['rancid']['os_map']['powerconnect-fastpath'] = 'dell';
#$config['rancid']['os_map']['powerconnect-radlan']   = 'dell';
#$config['rancid']['os_map']['dnos6']                 = 'dell';

//////////////////////////////////////////////////////////////////////////
// No changes below this line // (no changes above it either, remember? //
//////////////////////////////////////////////////////////////////////////

// Include DB functions

define('OBS_DB_LINK', 'observium_link'); // Global variable name for DB link identifier, required for mysqli
$config['db_extension'] = strtolower($config['db_extension']);
switch ($config['db_extension'])
{
  case 'mysqli':
    define('OBS_DB_EXTENSION', $config['db_extension']);
    break;
  case 'mysql':
  default:
    define('OBS_DB_EXTENSION', 'mysql');
}
require_once($config['install_dir'] . "/includes/db.inc.php");

// Include paths for PEAR
set_include_path($config['install_dir'] . "/libs/pear" . PATH_SEPARATOR . get_include_path());

include($config['install_dir'].'/includes/definitions/version.inc.php');

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
if (!isset($config['template_dir'])) { $config['template_dir'] = $config['install_dir'] . '/templates'; }
else                                 { $config['template_dir'] = rtrim($config['template_dir'], ' /'); }

// Connect to database
$GLOBALS[OBS_DB_LINK] = dbOpen($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// Connect to statsd

if ($config['statsd']['enable'] && class_exists('StatsD'))
{
  $statsd = new StatsD($config['statsd']['host'].':'.$config['statsd']['port']);
}

// Base user levels

$config['user_level']     = array(); // Init this array, for do not allow override over config.inc.php!
$config['user_level'][0]  = array('permission' => 0,
                                  'name'       => 'Disabled',
                                  'subtext'    => 'This user disabled',
                                  'row_class'  => 'disabled',
                                  'icon'       => 'oicon-user--minus');
$config['user_level'][1]  = array('permission' => OBS_PERMIT_ACCESS,
                                  'name'       => 'Normal User',
                                  'subtext'    => 'This user has read access to individual entities',
                                  'row_class'  => '',
                                  'icon'       => 'oicon-user--plus');
$config['user_level'][5]  = array('permission' => OBS_PERMIT_ACCESS | OBS_PERMIT_READ,
                                  'name'       => 'Global Read',
                                  'subtext'    => 'This user has global read access',
                                  'row_class'  => 'suppressed',
                                  'icon'       => 'oicon-user-business');
$config['user_level'][7]  = array('permission' => OBS_PERMIT_ACCESS | OBS_PERMIT_READ | OBS_PERMIT_SECURE,
                                  'name'       => 'Secure Global Read',
                                  'subtext'    => 'This user has global read access with secured info',
                                  'row_class'  => 'recovery',
                                  'icon'       => 'oicon-user-business');
$config['user_level'][10] = array('permission' => OBS_PERMIT_ALL,
                                  'name'       => 'Administrator',
                                  'subtext'    => 'This user has full administrative access',
                                  'row_class'  => 'success',
                                  'icon'       => 'oicon-user-detective');

$config['remote_access']['ssh']    = array('name' => "SSH",    'port' => '22',   'icon' => 'oicon-application-terminal');
$config['remote_access']['telnet'] = array('name' => "Telnet", 'port' => '23',   'icon' => 'oicon-application-list');
$config['remote_access']['scp']    = array('name' => "SFTP",   'port' => '22',   'icon' => 'oicon-disk-black');
$config['remote_access']['ftp']    = array('name' => "FTP",    'port' => '21',   'icon' => 'oicon-disk');
$config['remote_access']['http']   = array('name' => "HTTP",   'port' => '80',   'icon' => 'oicon-application-icon-large');
$config['remote_access']['https']  = array('name' => "HTTPS",  'port' => '443',  'icon' => 'oicon-shield');
$config['remote_access']['rdp']    = array('name' => "RDP",    'port' => '3389', 'icon' => 'oicon-connect');
$config['remote_access']['vnc']    = array('name' => "VNC",    'port' => '5901', 'icon' => 'oicon-computer');

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
$config['device_tables'] = array(
  'accesspoints', 'alert_log', 'alert_table', 'applications', 'bgpPeers', 'bgpPeers_cbgp',
  'cef_prefix', 'cef_switching', 'devices_mibs', 'devices_locations', 'devices_perftimes',
  'device_graphs', 'eigrp_ports', 'entPhysical', 'eventlog', 'hrDevice', 'ipsec_tunnels',
  'loadbalancer_rservers', 'loadbalancer_vservers', 'mempools', 'munin_plugins', 'netscaler_services',
  'netscaler_services_vservers', 'netscaler_vservers', 'ospf_areas', 'ospf_instances',
  'ospf_nbrs', 'ospf_ports', 'packages', 'ports', 'ports_stack', 'ports_vlans', 'processors',
  'pseudowires', 'sensors', 'status', 'services', 'slas', 'storage', 'syslog', 'toner',
  'ucd_diskio', 'vlans', 'vlans_fdb', 'vminfo', 'vrfs', 'wifi_accesspoints', 'wifi_sessions',
  'group_table', 'p2p_radios', 'oids_assoc', 'lb_virtuals',
  'devices' // always leave the table devices as last
);


// Generate proper device types

// $config['device_types'][$i]['type'] = 'management';

foreach ($config['device_types'] AS $device_order => $device_type)
{
  $config['devicetypes'][$device_type['type']] = $device_type;
  $config['devicetypes'][$device_type['type']]['order'] = $device_order;
}

// Obsolete config variables
// Note, for multiarray config options use conversion with '->'
// example: $config['email']['default'] --> 'email->default'
$config['obsolete_config'] = array(); // NOT CONFIGURABLE, init
$config['obsolete_config'][] = array('old' => 'warn->ifdown',        'new' => 'frontpage->device_status->ports');
$config['obsolete_config'][] = array('old' => 'alerts->email->enable',       'new' => 'email->enable',       'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'alerts->email->default',      'new' => 'email->default',      'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'alerts->email->default_only', 'new' => 'email->default_only', 'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'alerts->email->graphs',       'new' => 'email->graphs',       'info' => 'changed since r6976');
$config['obsolete_config'][] = array('old' => 'email_backend',       'new' => 'email->backend',       'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_from',          'new' => 'email->from',          'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_sendmail_path', 'new' => 'email->sendmail_path', 'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_host',     'new' => 'email->smtp_host',     'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_port',     'new' => 'email->smtp_port',     'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_timeout',  'new' => 'email->smtp_timeout',  'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_secure',   'new' => 'email->smtp_secure',   'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_auth',     'new' => 'email->smtp_auth',     'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_username', 'new' => 'email->smtp_username', 'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'email_smtp_password', 'new' => 'email->smtp_password', 'info' => 'changed since r5787');
$config['obsolete_config'][] = array('old' => 'discovery_modules->cisco-pw', 'new' => 'discovery_modules->pseudowires', 'info' => 'changed since r6205');
$config['obsolete_config'][] = array('old' => 'discovery_modules->discovery-protocols', 'new' => 'discovery_modules->neighbours', 'info' => 'changed since r6744');
$config['obsolete_config'][] = array('old' => 'search_modules',      'new' => 'wui->search_modules', 'info' => 'changed since r7463');

// Here whitelist of base definitions keys which can be overridden by config.php file
// Note, this required only for override already exist definitions, for additions not required
$config['definitions_whitelist'] = array('os', 'rancid', 'geo_api', 'search_modules', 'rewrites', 'nicecase', 'wui');

// End of includes/definitions.inc.php
