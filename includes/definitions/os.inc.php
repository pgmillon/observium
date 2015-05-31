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

/*
 * Notes about 'os' definitions.
 *
 * $os - is main OS name. Used for per 'os' purposes in poller/discovery/web
 * $os_group - same as $os, but uses common options for this group
 *
 * $config['os'][$os]['group'] - sets os_group for this os
 * $config['os'][$os]['type'] - sets type for this os. Must be one of specified in $config['device_types']
 * $config['os'][$os]['discovery_os'] - for now this is used only for get_device_os().
 *                                      Used when OS discovered by filename does not match includes/discovery/os/$os.inc.php
 * FIXME. Other definitions.
 *
 * WEB:
 * $config['os'][$os]['text'] - is OS name displayed on web pages
 * $config['os'][$os]['icon'] - icon name displayed for os
 * $config['os'][$os]['over'] - this is displaying options for a web pages
 *
 */

$config['os']['default']['over'][0]['graph']            = "device_bits";
$config['os']['default']['over'][0]['text']             = "Traffic";
$config['os']['default']['over'][1]['graph']            = "device_uptime";
$config['os']['default']['over'][1]['text']             = "Uptime";
$config['os']['default']['over'][2]['graph']            = "device_ping";
$config['os']['default']['over'][2]['text']             = "Ping Response";

$os_group     = "unix";
$config['os_group'][$os_group]['type']                  = "server";
$config['os_group'][$os_group]['processor_stacked']     = 1;
$config['os_group'][$os_group]['over'][0]['graph']      = "device_processor";
$config['os_group'][$os_group]['over'][0]['text']       = "Processors";
$config['os_group'][$os_group]['over'][1]['graph']      = "device_ucd_memory";
$config['os_group'][$os_group]['over'][1]['text']       = "Memory";
$config['os_group'][$os_group]['mibs'][]                = "UCD-SNMP-MIB";
$config['os_group'][$os_group]['mibs'][]                = "LSI-MegaRAID-SAS-MIB";

$os_group     = "cisco";
$config['os_group'][$os_group]['type']                  = "network";
$config['os_group'][$os_group]['over'][0]['graph']      = "device_bits";
$config['os_group'][$os_group]['over'][0]['text']       = "Traffic";
$config['os_group'][$os_group]['over'][1]['graph']      = "device_processor";
$config['os_group'][$os_group]['over'][1]['text']       = "CPU Usage";
$config['os_group'][$os_group]['over'][2]['graph']      = "device_mempool";
$config['os_group'][$os_group]['over'][2]['text']       = "Memory";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-IETF-IP-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-ENTITY-SENSOR-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-VTP-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-ENVMON-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-ENTITY-QFP-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-IP-STAT-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-FIREWALL-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-ENHANCED-MEMPOOL-MIB";
$config['os_group'][$os_group]['mibs'][]                = "CISCO-MEMORY-POOL-MIB"; // Keep this below CISCO-ENHANCED-MEMPOOL-MIB, checks for duplicates.
$config['os_group'][$os_group]['mibs'][]                = "CISCO-PROCESS-MIB"; // Goes after "CISCO-MEMORY-POOL-MIB" and "CISCO-ENHANCED-MEMPOOL-MIB" cos Cisco suck.

$os = "generic";
$config['os'][$os]['text']                  = "Generic Device";

// Linux-based OSes here please.

$os = "linux";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Linux";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "CPU Load";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][2]['text']       = "Storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['over'][3]['text']       = "Traffic";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";
$config['os'][$os]['mibs'][]                = "SUPERMICRO-HEALTH-MIB";
$config['os'][$os]['mibs'][]                = "MIB-Dell-10892";
$config['os'][$os]['mibs'][]                = "CPQHLTH-MIB";
$config['os'][$os]['mibs'][]                = "CPQIDA-MIB";
$config['os'][$os]['realtime']              = 15;

$os = "vmware";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['text']                  = "VMware";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = '.1.3.6.1.4.1.6876.4.1';
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";

$os = "qnap";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['text']                  = "QNAP TurboNAS";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['realtime']              = 15;

$os = "dss";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['text']                  = "Open-E DSS";
$config['os'][$os]['icon']                  = "open-e";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['realtime']              = 15;

$os = "vyatta";
$config['os'][$os]['text']                  = "Vyatta";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "endian";
$config['os'][$os]['text']                  = "Endian";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "openwrt";
$config['os'][$os]['text']                  = "OpenWrt";
$config['os'][$os]['type']                  = "network"; /// Or wireless, or firewalll?
//$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "ddwrt";
$config['os'][$os]['text']                  = "DD-WRT";
$config['os'][$os]['type']                  = "network"; /// Or wireless, or firewalll?
//$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "wut";
$config['os'][$os]['text']                  = "Web-Thermograph";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "Temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5040.1";
$config['os'][$os]['mibs'][]                = "WebGraph-8xThermometer-US-MIB";

$os = "terastation";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "BUFFALO TeraStation";
$config['os'][$os]['icon']                  = "buffalo";

// Check Point

$os = "ipso";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Check Point IPSO"; // Old vendor NOKIA
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.94.1.21.2.1";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";
$config['os'][$os]['mibs'][]                = "NOKIA-IPSO-SYSTEM-MIB";

$os = "sofaware";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Check Point Embedded NGX";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6983.1";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";
$config['os'][$os]['mibs'][]                = "EMBEDDED-NGX-MIB";

$os = "infoblox";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Infoblox";
$config['os'][$os]['icon']                  = "infoblox";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.7779.1";
$config['os'][$os]['mibs'][]                = "IB-DNSONE-MIB";
$config['os'][$os]['mibs'][]                = "IB-DHCPONE-MIB";
$config['os'][$os]['mibs'][]                = "IB-PLATFORMONE-MIB";

$os = "splat";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Check Point SecurePlatform";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";

$os = "gaia";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Check Point GAiA";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";

$os = "infratec-rms";
$config['os'][$os]['text']                  = "Infratec RMS";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1909.10";
$config['os'][$os]['mibs'][]                = "INFRATEC-RMS-MIB";

// Other Unix-based OSes here please.

$os = "ibmi";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "IBM System i";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.6.11";

$os = "freebsd";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['text']                  = "FreeBSD";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.8";

$os = "openbsd";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "OpenBSD";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30155.23.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.12"; // Net-SNMP
$config['os'][$os]['mibs'][]                = "OPENBSD-SENSORS-MIB";

$os = "netbsd";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "NetBSD";

$os = "dragonfly";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "DragonflyBSD";

$os = "netware";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['text']                  = "Novell Netware";
$config['os'][$os]['icon']                  = "novell";

$os = "darwin";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Mac OS X";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";

$os = "monowall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "m0n0wall";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "pfsense";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "pfSense";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "freenas";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "FreeNAS";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "Processors";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][2]['text']       = "Storage";

$os = "nas4free";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "NAS4Free";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "Processors";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][2]['text']       = "Storage";

$os = "solaris";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";
$config['os'][$os]['text']                  = "Sun Solaris";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.42.2.1.1";

$os = "opensolaris";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";
$config['os'][$os]['text']                  = "Sun OpenSolaris";

$os = "openindiana";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";
$config['os'][$os]['text']                  = "OpenIndiana";

$os = "nexenta";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";
$config['os'][$os]['text']                  = "NexentaOS";

$os = "nestos";
$config['os'][$os]['text']                  = "Nexsan NST";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "nexsan";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.7247.1.1";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";

$os = "aix";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "AIX";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['ifAliasSemicolon']      = TRUE;             // Split on semicolon and take the first element.
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "CPU Load";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";  
$config['os'][$os]['over'][2]['text']       = "Storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['over'][3]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.3.1.2.1.1.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.3.1.2.1.1.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.15";

$os = "adva";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['text']                  = "Adva Optical";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1671";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2544";
$config['os'][$os]['mib_dirs'][]            = "adva";
//$config['os'][$os]['mibs'][]                = "ADVA-MIB";
$config['os'][$os]['mibs'][]                = "FspR7-MIB";

$os = "equallogic";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['text']                  = "Storage Array Firmware";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12740.17.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12740.12.1.1.0";
$config['os'][$os]['mibs'][]                = "EQLMEMBER-MIB";
$config['os'][$os]['mibs'][]                = "EQLDISK-MIB";

// AdTran

$os = "adtran-aos";
$config['os'][$os]['group']                 = "adtran-aos";
$config['os'][$os]['text']                  = "ADTRAN AOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "adtran";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.664.1";
$config['os'][$os]['mib_dirs'][]            = "adtran";
$config['os'][$os]['mibs'][]                = "ADTRAN-AOSCPU";

// Alcatel

$os = "aos";
$config['os'][$os]['group']                 = "aos";
$config['os'][$os]['text']                  = "Alcatel-Lucent OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "alcatellucent";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-HEALTH-MIB";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-INTERSWITCH-PROTOCOL-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.800.1.1.2.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.801.1.1.2.1";

$os = "timos";
$config['os'][$os]['group']                 = "timos";
$config['os'][$os]['text']                  = "Alcatel-Lucent TimOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "alcatellucent";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-TIMETRA-CHASSIS-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6527.";

// Cisco

$os = "ios";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco IOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['icon']                  = "cisco";

$os = "acsw";
#$config['os'][$os]['group']                = "cisco";
$config['os'][$os]['text']                  = "Cisco ACE";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1291";
$config['os'][$os]['mibs'][]                = "CISCO-PROCESS-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-SLB-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-ENHANCED-SLB-MIB";

$os = "iosxe";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco IOS-XE";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['ifXmcbc']               = 1;
# $config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['icon']                  = "cisco";

$os = "iosxr";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco IOS-XR";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['icon']                  = "cisco";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "asa";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco ASA";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";

$os = "fwsm";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco Firewall Service Module";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";

$os = "pixos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco PIX-OS";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "nxos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco NX-OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
// $config['os'][$os]['snmp']['max-rep']       = 100; # issues apparent
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "sanos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco SAN-OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "catos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco CatOS";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['icon']                  = "cisco-old";
$config['os'][$os]['snmp']['max-rep']       = 20;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "wlc";
$config['os'][$os]['text']                  = "Cisco WLC";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['mibs'][]                = "AIRESPACE-WIRELESS-MIB";

$os = "cisco-ons";
$config['os'][$os]['text']                  = "Cisco Cerent ONS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3607.";
//$config['os'][$os]['mibs'][]                = "CERENT-ENVMON-MIB"; // Not implemented
//$config['os'][$os]['mibs'][]                = "CERENT-OPTICAL-MONITOR-MIB"; // Not implemented

$os = "cisco-lms";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Cisco Prime LMS";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['discovery_os']          = "cisco";

$os = "cisco-acs";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['text']                  = "Cisco Secure ACS";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['discovery_os']          = "cisco";

$os = "ciscosmblinux";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['text']                  = "Cisco SMB Linux";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

$os = "meraki";
$config['os'][$os]['text']                  = "Cisco Meraki";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "meraki";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.29671.1"; // Cloud controller
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.29671.2.";
$config['os'][$os]['mibs'][]                = "IEEE802dot11-MIB";
$config['os'][$os]['mibs'][]                = "MERAKI-CLOUD-CONTROLLER-MIB";


// Cisco UCS CIMC

$os = "cimc";
$config['os'][$os]['text']                  = "Cisco Integrated Management Controller";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "Temperature";
$config['os'][$os]['over'][1]['graph']      = "device_power";
$config['os'][$os]['over'][1]['text']       = "Power";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1512";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1513";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1514";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1515";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1516";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1682";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1683";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1684";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1685";
$config['os'][$os]['mibs'][]                = "CISCO-UCS-CIMC-MIB";

// Cisco IronPort

$os = "asyncos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco IronPort";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['mibs'][]                = "ASYNCOS-MAIL-MIB";

// Cisco Small Business (Linksys)

$os = "ciscosb";
#$config['os'][$os]['group']                 = "cisco"; // Cisco SB is not Cisco! --mike
$config['os'][$os]['text']                  = "Cisco Small Business";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ciscosb";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.80.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.81.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.82.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.83.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.85.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.89.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.11.82.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3955.";
$config['os'][$os]['mibs'][]                = "CISCOSB-rndMng";

// Cisco Service Control OS / SCE

$os = "ciscoscos";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['text']                  = "Cisco Service Control OS";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";

// Huawei

$os = "vrp";
$config['os'][$os]['group']                 = "vrp";
$config['os'][$os]['text']                  = "Huawei VRP";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "huawei";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.";
$config['os'][$os]['mibs'][]                = "HUAWEI-ENTITY-EXTENT-MIB";

// ZTE

$os = "zxr10";
$config['os'][$os]['group']                 = "zxr10";
$config['os'][$os]['text']                  = "ZTE ZXR10";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zte";

// Netgear

$os = "netgear";
$config['os'][$os]['text']                  = "Netgear";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "netgear";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4526";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12622";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['mibs'][]                = "UCD-SNMP-MIB";

// Korenix

$os = "korenix-jetnet";
$config['os'][$os]['text']                  = "Korenix Jetnet";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "korenix";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.3";

// Supermicro Switch

$os = "supermicro-switch";
$config['os'][$os]['group']                 = "supermicro";
$config['os'][$os]['text']                  = "Supermicro Switch";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "supermicro";
$config['os'][$os]['ifname']                = 1;

// Juniper

$os = "junos";
$config['os'][$os]['text']                  = "Juniper JunOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
// $config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['discovery_blacklist'][] = "entity-sensor";
$config['os'][$os]['discovery_blacklist'][] = "entity-physical";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2636";
$config['os'][$os]['mibs'][]                = "JUNIPER-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-ALARM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-DOM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-SRX5000-SPU-MONITORING-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-VLAN-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-MAC-MIB";

$os = "junose";
$config['os'][$os]['text']                  = "Juniper JunOSe";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
//$config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4874";
$config['os'][$os]['mibs'][]                = "JUNIPER-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-DOM-MIB";
$config['os'][$os]['mibs'][]                = "Juniper-System-MIB";

$os = "jwos";
$config['os'][$os]['text']                  = "Juniper JWOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8239.1.2.9";

$os = "screenos";
$config['os'][$os]['text']                  = "Juniper ScreenOS";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.3224.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3224";
$config['os'][$os]['mibs'][]                = "NETSCREEN-RESOURCE-MIB";

$os = "juniperive";
$config['os'][$os]['text']                  = "Juniper IVE";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12532";
$config['os'][$os]['mibs'][]                = "JUNIPER-IVE-MIB";

// Fortinet

$os = "fortigate";
$config['os'][$os]['text']                  = "Fortinet Fortigate";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "fortinet";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_fortigate_cpu";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12356.15";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12356.101.1";
$config['os'][$os]['mibs'][]                = "FORTINET-FORTIGATE-MIB";

// BTI Systems

$os = "bti7000";
$config['os'][$os]['text']                  = "BTI 7000";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "bti";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.18070.2.2";

// Ciena

$os = "ciena";
$config['os'][$os]['text']                  = "SAOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ciena";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Device Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory Usage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6141.1";
$config['os'][$os]['mibs'][]                = "WWP-LEOS-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "WWP-LEOS-PORT-XCVR-MIB";
$config['os'][$os]['mibs'][]                = "CIENA-TOPSECRET-MIB"; // Not really, but meh. -TL

// Mikrotik

$os = "routeros";
$config['os'][$os]['text']                  = "Mikrotik RouterOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mikrotik";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "MIKROTIK-MIB";

// Brocade / Foundry

$os = "ironware";
$config['os'][$os]['text']                  = "Brocade IronWare";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['snmp']['max-rep']       = 60;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.3";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-SWITCH-GROUP-MIB";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-AGENT-MIB";

$os = "fabos";
$config['os'][$os]['text']                  = "Brocade FabricOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1588.2.1.1";
$config['os'][$os]['mibs'][]                = "SW-MIB";

$os = "nos";
$config['os'][$os]['text']                  = "Brocade NOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['mibs'][]                = "SW-MIB";

// Extreme

$os = "xos";
$config['os'][$os]['text']                  = "Extreme XOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "extremeware";
$config['os'][$os]['discovery_os']          = "extremeware";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['icon']                  = "extreme";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
#$config['os'][$os]['over'][1]['graph']      = "device_processor";
#$config['os'][$os]['over'][1]['text']       = "CPU Usage";
#$config['os'][$os]['over'][2]['graph']      = "device_mempool";
#$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "EXTREME-BASE-MIB";

$os = "extremeware";
$config['os'][$os]['text']                  = "Extremeware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "extreme";
$config['os'][$os]['discovery_os']          = "extremeware";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "EXTREME-BASE-MIB"; // Probably?

// Bluecoat

$os = "packetshaper";
$config['os'][$os]['text']                  = "Blue Coat Packetshaper";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "bluecoat";

$os = "proxysg";
$config['os'][$os]['text']                  = "Blue Coat Proxy SG";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.2.11";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.1.1";
$config['os'][$os]['icon']                  = "bluecoat";
$config['os'][$os]['mibs'][]                = "BLUECOAT-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-SG-PROXY-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-SG-SENSOR-MIB";

// $config['os'][$os]['mibs'][]                = "BLUECOAT-SG-ICAP-MIB";

// Force 10

$os = "zhonedslam";
$config['os'][$os]['text']                  = "Zhone DLSAM";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zhone";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1795";

// Force 10

$os = "ftos";
$config['os'][$os]['text']                  = "Force10 FTOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "force10";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "F10-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "F10-C-SERIES-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "F10-S-SERIES-CHASSIS-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.1"; // f10ESeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.2"; // f10CSeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.3"; // f10SSeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.4"; // f10MSeriesProducts

// Avaya

$os = "avaya-ers";
$config['os'][$os]['text']                  = "ERS Firmware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avaya";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.45.3";
$config['os'][$os]['mibs'][]                = "S5-CHASSIS-MIB";

// Arista

$os = "arista_eos";
$config['os'][$os]['text']                  = "Arista EOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "arista";
// $config['os'][$os]['snmp']['max-rep']       = 100; // Seems to break.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "ARISTA-ENTITY-SENSOR-MIB";

// Calix

$os = "calix";
$config['os'][$os]['text']                  = "Calix";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "calix";
$config['os'][$os]['snmp']['max-rep']       = 30; // More - breaks, less or nobulk - very slow polling and discovery
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6321";
$config['os'][$os]['mibs'][]                = "E7-Calix-MIB";

// Citrix

$os = "netscaler";
$config['os'][$os]['text']                  = "Citrix Netscaler";
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "citrix";
// $config['os'][$os]['snmp']['max-rep']       = 50; // Seems to break
$config['os'][$os]['over'][0]['graph']      = "device_netscaler_tcp_conn";
$config['os'][$os]['over'][0]['text']       = "TCP Connections";
$config['os'][$os]['over'][1]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['text']       = "Traffic";
$config['os'][$os]['over'][2]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['text']       = "CPU Usage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5951.1";
$config['os'][$os]['mibs'][]                = "NS-ROOT-MIB";

// F5

$os = "f5";
$config['os'][$os]['text']                  = "F5 BIG-IP";
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "f5";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3375.2.1.3.4.";
$config['os'][$os]['mibs'][]                = "F5-BIGIP-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "F5-BIGIP-LOCAL-MIB";
$config['os'][$os]['mibs'][]                = "F5-BIGIP-GLOBAL-MIB";
$config['os'][$os]['mibs'][]                = "F5-BIGIP-APM-MIB";

// PacketFlux

$os = "sitemonitor";
$config['os'][$os]['text']                  = "PacketFlux SiteMonitor";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "packetflux";
$config['os'][$os]['over'][0]['graph']      = "device_voltage";
$config['os'][$os]['over'][0]['text']       = "Voltage";
$config['os'][$os]['over'][1]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['text']       = "Temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.32050";
$config['os'][$os]['mibs'][]                = "PACKETFLUX-MIB";

// Cambium Canopy
$os = "canopy";
$config['os'][$os]['text']                  = "Cambium Canopy";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "cambium";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.161.19.250.256";
$config['os'][$os]['mibs'][]                = "CANOPY-MIB";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['over'][1]['text']       = "Wireless Clients";

// Cambium PTP800
$os = "ptp800";
$config['os'][$os]['text']                  = "Cambium PTP800";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "cambium";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.17713.8";
$config['os'][$os]['mibs'][]                = "CAMBIUM-PTP800-V2-MIB";

// Cambium PTP400/600
$os = "ptp400";
$config['os'][$os]['text']                  = "Cambium PTP400/600";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "cambium";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.17713.1";
$config['os'][$os]['mibs'][]                = "MOTOROLA-PTP-MIB";

// Proxim

$os = "proxim";
$config['os'][$os]['text']                  = "Proxim";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "proxim";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11898.2.4.9";

// Dell

/// This is only to be used for Dell Network Operating System (DNOS) Devices

$os = 'dnos';
$config['os'][$os]['text']                  = 'Dell Networking (DNOS)';
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = 'network';
$config['os'][$os]['icon']                  = 'dell';
$config['os'][$os]['over'][0]['graph']      = 'device_bits';
$config['os'][$os]['over'][0]['text']       = 'Traffic';
$config['os'][$os]['over'][1]['graph']      = 'device_processor';
$config['os'][$os]['over'][1]['text']       = 'CPU Usage';
$config['os'][$os]['over'][2]['graph']      = 'device_mempool';
$config['os'][$os]['over'][2]['text']       = 'Memory';
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3023";  // 8024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3024";  // 8024F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3042";  // N4032
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3044";  // N4032F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3045";  // N4064
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3046";  // N4064F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3053";  // N2024
$config['os'][$os]['sysObjectID'][]         = '.1.3.6.1.4.1.674.10895.3054';  // N2048
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3055";  // N2024P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3056";  // N2048P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3057";  // N3024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3058";  // N3048
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3059";  // N3024P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3060";  // N3048P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3061";  // N3024F
$config['os'][$os]['mib_dirs'][]            = 'dell';
$config['os'][$os]['mibs'][]                = 'DNOS-SWITCHING-MIB';
$config['os'][$os]['mibs'][]                = 'DNOS-BOXSERVICES-PRIVATE-MIB';

/// This is only to be used for Broadcom-based PowerConnects

$os = "powerconnect-fastpath";
$config['os'][$os]['text']                  = "Dell PowerConnect (BCM)";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3010";  // 6224
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3011";  // 6248
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3012";  // 6224P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3013";  // 6248P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3014";  // 6224F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3015";  // M6220
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3022";  // M8024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3025";  // M6384
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3026";  // 2824
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3027";  // 2848
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3034";  // 7024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3035";  // 7048
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3036";  // 7024P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3037";  // 7048P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3038";  // 7024F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3039";  // 7048R
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3040";  // 7048R-RA
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3041";  // M8024-k
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3052";  // VRTX R1-2401
$config['os'][$os]['mibs'][]                = "FASTPATH-BOXSERVICES-PRIVATE-MIB";
$config['os'][$os]['mibs'][]                = "Dell-Vendor-MIB"; // Keep this below FASTPATH-BOXSERVICES-PRIVATE-MIB, checks for duplicate sensors

// This is only to be used for RADLAN-based PowerConnects

$os = "powerconnect-radlan";
$config['os'][$os]['text']                  = "Dell PowerConnect (RADLAN)";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3000"; // 6024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3003"; // 3348
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3004"; // 5324
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3016"; // 3534
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3020"; // 5424
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3021"; // 5448
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3028"; // 2824
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3029"; // 2848
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3030"; // 5524
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3031"; // 5548
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3032"; // 5524P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3033"; // 5548P
$config['os'][$os]['mibs'][]                = "RADLAN-HWENVIROMENT";
#$config['os'][$os]['mibs'][]                = "Dell-Vendor-MIB"; // Keep this below RADLAN-HWENVIROMENT, checks for duplicate sensors
$config['os'][$os]['mibs'][]                = "RADLAN-rndMng";

$os = "powervault";
$config['os'][$os]['text']                  = "Dell PowerVault";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10893.2.102";

$os = "drac";
$config['os'][$os]['text']                  = "Dell iDRAC";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.5";
$config['os'][$os]['mib_dirs'][]            = "dell";
$config['os'][$os]['mibs'][]                = "DELL-RAC-MIB";

// Broadcom

$os = "bcm963";
$config['os'][$os]['text']                  = "Broadcom BCM963xx";
$config['os'][$os]['icon']                  = "broadcom";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";

// Procera

$os = "plos";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['text']                  = "Packet Logic";
$config['os'][$os]['ifXmcbc']               = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "CPU Load";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";  
$config['os'][$os]['over'][2]['text']       = "Storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['over'][3]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "procera";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.15397.2";

// Mellanox
$os = "mlnx-os";
$config['os'][$os]['group']                 = "mellanox";
$config['os'][$os]['text']                  = "MLNX-OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mellanox";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.33049";

// Motorola

$os = "netopia";
$config['os'][$os]['text']                  = "Motorola Netopia";
$config['os'][$os]['type']                  = "network";

// Tranzeo

$os = "tranzeo";
$config['os'][$os]['text']                  = "Tranzeo";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";

// Exalt

$os = "exalt";
$config['os'][$os]['text']                  = "Exalt";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25651.1.2";
$config['os'][$os]['mibs'][]                = "ExaltComProducts";

// Alvarion

$os = "breeze";
$config['os'][$os]['text']                  = "Alvarion";
$config['os'][$os]['icon']                  = "alvarion";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['over'][1]['text']       = "Wireless clients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12394.4.1.";
$config['os'][$os]['mibs'][]                = "ALVARION-DOT11-WLAN-MIB";

$os = "breezemax";
$config['os'][$os]['text']                  = "Alvarion";
$config['os'][$os]['icon']                  = "alvarion";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12394.1.";
//$config['os'][$os]['mibs'][]                = "ALVARION-DOT11-WLAN-MIB";

// D-Link

$os = "dlink";
$config['os'][$os]['text']                  = "D-Link Switch";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "dlink";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mibs'][]                = "AGENT-GENERAL-MIB";

$os = "dlinkap";
$config['os'][$os]['text']                  = "D-Link Access Point";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "dlink";

// AXIS

$os = "axiscam";
$config['os'][$os]['text']                  = "AXIS Network Camera";
$config['os'][$os]['icon']                  = "axis";

$os = "axisdocserver";
$config['os'][$os]['text']                  = "AXIS Network Document Server";
$config['os'][$os]['icon']                  = "axis";

// Gamatronic

$os = "gamatronicups";
$config['os'][$os]['text']                  = "Gamatronic UPS Stack";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['mibs'][]                = "GAMATRONIC-MIB";

// Powerware

$os = "powerware";
$config['os'][$os]['text']                  = "Powerware UPS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "eaton";
$config['os'][$os]['over'][0]['graph']      = "device_voltage";
$config['os'][$os]['over'][0]['text']       = "Voltage";
$config['os'][$os]['over'][1]['graph']      = "device_current";
$config['os'][$os]['over'][1]['text']       = "Current";
$config['os'][$os]['over'][2]['graph']      = "device_frequency";
$config['os'][$os]['over'][2]['text']       = "Frequency";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.534";
$config['os'][$os]['mibs'][]                = "XUPS-MIB";

// Delta

$os = "deltaups";
$config['os'][$os]['text']                  = "Delta UPS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "delta";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2254.2.4";
$config['os'][$os]['mibs'][]                = "DeltaUPS-MIB";

// Liebert

$os = "liebert";
$config['os'][$os]['text']                  = "Liebert";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "liebert";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.476.1.42";
$config['os'][$os]['mibs'][]                = "UPS-MIB";

// Engenius

$os = "engenius";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['text']                  = "EnGenius Access Point";
$config['os'][$os]['icon']                  = "engenius";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['over'][1]['text']       = "Wireless clients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14125.100.1.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14125.101.1.3";
$config['os'][$os]['mibs'][]                = "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB";
$config['os'][$os]['mibs'][]                = "ENGENIUS-PRIVATE-MIB";
$config['os'][$os]['mibs'][]                = "ENGENIUS-MESH-MIB";

// Apple

$os = "airport";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['text']                  = "Apple AirPort";
$config['os'][$os]['icon']                  = "apple";

// Microsoft

$os = "windows";
$config['os'][$os]['text']                  = "Microsoft Windows";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['processor_stacked']     = 1;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][0]['text']       = "CPU Load";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][1]['text']       = "Memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][2]['text']       = "Storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['over'][3]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.311.1.1.3";
$config['os'][$os]['mibs'][]                = "LSI-MegaRAID-SAS-MIB";
$config['os'][$os]['mibs'][]                = "MIB-Dell-10892";

// Blade Network Technologies

$os = "bnt";
$config['os'][$os]['text']                  = "Blade Network Technologies";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "bnt";

// NetAPP

$os = "netapp";
$config['os'][$os]['text']                  = "NetApp";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "netapp";
$config['os'][$os]['snmp']['max-rep']       = 50;
$config['os'][$os]['over'][0]['graph']      = "device_netapp_net_io";
$config['os'][$os]['over'][0]['text']       = "Network Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_netapp_ops";
$config['os'][$os]['over'][1]['text']       = "Operations";
$config['os'][$os]['over'][2]['graph']      = "device_netapp_disk_io";
$config['os'][$os]['over'][2]['text']       = "Disk I/O";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.789.2.";
$config['os'][$os]['mibs'][]                = "NETAPP-MIB";

// Arris

$os = "arris-d5";
$config['os'][$os]['text']                  = "Arris D5";
$config['os'][$os]['type']                  = "video";
$config['os'][$os]['icon']                  = "arris";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4115.1.8.1";

$os = "arris-c3";
$config['os'][$os]['text']                  = "Arris C3";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "arris";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4115.1.4.3";

// HP / 3Com

$os = "3com";
$config['os'][$os]['text']                  = "3Com OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "3com";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.43";

$os = "procurve";
$config['os'][$os]['text']                  = "HP ProCurve";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.7.11.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.7.8.";
$config['os'][$os]['mibs'][]                = "STATISTICS-MIB";
$config['os'][$os]['mibs'][]                = "NETSWITCH-MIB";
$config['os'][$os]['mibs'][]                = "HP-ICF-CHASSIS";

$os = "h3c";
$config['os'][$os]['text']                  = "H3C Comware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "h3c";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.10";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25506.1.";

$os = "hh3c";
$config['os'][$os]['text']                  = "HP Comware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25506";
$config['os'][$os]['mibs'][]                = "HH3C-ENTITY-EXT-MIB";

$os = "speedtouch";
$config['os'][$os]['text']                  = "Thomson Speedtouch";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";

$os = "sonicwall";
$config['os'][$os]['text']                  = "SonicWALL";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";

// ZyXEL

$os = "zywall";
$config['os'][$os]['text']                  = "ZyXEL ZyWALL";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['icon']                  = "zyxel";
$config['os'][$os]['discovery_os']          = "zyxel";

$os = "prestige";
$config['os'][$os]['text']                  = "ZyXEL Prestige";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zyxel";
$config['os'][$os]['discovery_os']          = "zyxel";

$os = "zyxeles";
$config['os'][$os]['text']                  = "ZyXEL Ethernet Switch";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zyxel";
$config['os'][$os]['discovery_os']          = "zyxel";

$os = "zyxelnwa";
$config['os'][$os]['text']                  = "ZyXEL NWA";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zyxel";
$config['os'][$os]['discovery_os']          = "zyxel";

$os = "ies";
$config['os'][$os]['text']                  = "ZyXEL DSLAM";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zyxel";
$config['os'][$os]['discovery_os']          = "zyxel";
$config['os'][$os]['mibs'][]                = "ZYXEL-AS-MIB";

$os = "allied";
$config['os'][$os]['text']                  = "AlliedWare";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "alliedtelesis";
// $config['os'][$os]['snmp']['max-rep']       = 100; ## Issues with alliedwareplus
$config['os'][$os]['mibs'][]                = "AT-SYSINFO-MIB";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207";
$config['os'][$os]['mib_dirs'][]            = "allied";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_fdb_count";
$config['os'][$os]['over'][1]['text']       = "FDB Usage";
$config['os'][$os]['over'][2]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['text']       = "CPU Usage";
$config['os'][$os]['over'][3]['graph']      = "device_mempool";
$config['os'][$os]['over'][3]['text']       = "Memory";

$os = "alliedwareplus";
$config['os'][$os]['text']                  = "AlliedWare Plus";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "alliedtelesis";
$config['os'][$os]['mib_dirs'][]            = "alliedwareplus";
$config['os'][$os]['discovery_os']          = "alliedtelesis";
// $config['os'][$os]['snmp']['max-rep']       = 100; ## Issues with alliedwareplus
$config['os'][$os]['mibs'][]                = "AT-SYSINFO-MIB";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207";

// This is only to be used for RADLAN-based PowerConnects

$os = "allied-radlan";
$config['os'][$os]['text']                  = "Allied Telesis (RADLAN)";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "alliedtelesis";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207.1.4.125"; // ATI 8000S
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207.1.4.126"; // ATI AT-8000S
$config['os'][$os]['mibs'][]                = "RADLAN-HWENVIROMENT";
$config['os'][$os]['mibs'][]                = "RADLAN-rndMng";

$os = "microsens";
$config['os'][$os]['text']                  = "Microsens";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['over'][1]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['text']       = "Traffic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3181.10.3";
$config['os'][$os]['mibs'][]                = "MS-SWITCH30-MIB";

$os = "mgeups";
$config['os'][$os]['text']                  = "MGE UPS";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "mge";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.705.1";
$config['os'][$os]['mibs'][]                = "MG-SNMP-UPS-MIB";

$os = "mgepdu";
$config['os'][$os]['text']                  = "MGE PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "mge";

$os = "enterasys";
$config['os'][$os]['text']                  = "Enterasys";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "enterasys";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5624.2.1.132";

$os = "apc";
$config['os'][$os]['text']                  = "APC OS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.318";
$config['os'][$os]['mibs'][]                = "PowerNet-MIB";

$os = "oec";
$config['os'][$os]['text']                  = "OEC PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_uptime";
$config['os'][$os]['over'][0]['text']       = "Uptime";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.29640.1.2.4";
$config['os'][$os]['mibs'][]                = "APNL-MODULAR-PDU-MIB";

$os = "netbotz";
$config['os'][$os]['text']                  = "APC Netbotz";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "apc";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "Temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['over'][1]['text']       = "Humidity";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5528";
$config['os'][$os]['mibs'][]                = "NETBOTZV2-MIB";

$os = "pcoweb";
$config['os'][$os]['text']                  = "Carel pCOWeb";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "Temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['over'][1]['text']       = "Humidity";
$config['os'][$os]['icon']                  = "carel";
$config['os'][$os]['icons'][]               = "uniflair";
$config['os'][$os]['mibs'][]                = "CAREL-ug40cdz-MIB";

$os = "netvision";
$config['os'][$os]['text']                  = "Socomec Net Vision";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['mibs'][]                = "SICONUPS-MIB";

$os = "areca";
$config['os'][$os]['text']                  = "Areca RAID Subsystem";
$config['os'][$os]['over'][0]['graph']      = "";
$config['os'][$os]['over'][0]['text']       = "";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.18928.1";
$config['os'][$os]['mibs'][]                = "ARECA-SNMP-MIB";

$os = "netmanplus";
$config['os'][$os]['text']                  = "NetMan Plus";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['mibs'][]                = "UPS-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5491.6";

$os = "cs121";
$config['os'][$os]['text']                  = "Generex UPS";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "generex";
$config['os'][$os]['mibs'][]                = "UPS-MIB";

$os = "sensorgateway";
$config['os'][$os]['text']                  = "ServerRoom Sensor Gateway";
$config['os'][$os]['group']                    = "environment";
$config['os'][$os]['icon']                    = "serverscheck";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['mibs'][]                = "SENSORGATEWAY-MIB";

$os = "sensorprobe";
$config['os'][$os]['text']                  = "AKCP SensorProbe";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "akcp";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['mibs'][]                = "SPAGENT-MIB";

$os = "roomalert";
$config['os'][$os]['text']                  = "AVTECH RoomAlert";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "avtech";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['over'][1]['text']       = "humidity";
$config['os'][$os]['mibs'][]                = "ROOMALERT24E-MIB";
$config['os'][$os]['mibs'][]                = "ROOMALERT4E-MIB";

$os = "minkelsrms";
$config['os'][$os]['text']                  = "Minkels RMS";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['discovery_os']          = "sensorprobe";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['mibs'][]                = "SPAGENT-MIB";

$os = "ipoman";
$config['os'][$os]['text']                  = "Ingrasys iPoMan";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "ingrasys";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['over'][1]['graph']      = "device_power";
$config['os'][$os]['over'][1]['text']       = "Power";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2468.1.4.2.1";
$config['os'][$os]['mibs'][]                = "IPOMANII-MIB";

$os = "wxgoos";
$config['os'][$os]['text']                  = "ITWatchDogs Goose";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['over'][1]['text']       = "humidity";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.17373";
$config['os'][$os]['mibs'][]                = "IT-WATCHDOGS-MIB-V3";

$os = "papouch";
$config['os'][$os]['text']                  = "Papouch Probe";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['mibs'][]                = "Papouch-SMI";

$os = "cometsystem-p85xx";
$config['os'][$os]['text']                  = "Comet System P85xx";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "comet";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['mibs'][]                = "P8510-MIB";

$os = "dell-laser";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Dell Laser";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10898.2.100.10";

$os = "ricoh";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Ricoh Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "ricoh";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.367.1.1";

$os = "lexmark";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Lexmark Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "lexmark";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.641.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.641.2";

$os = "nrg";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "NRG Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "nrg";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "epson";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Epson Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "epson";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "xerox";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Xerox Printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.253.8.62.1.19.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.253.8.62.1.20.";

$os = "jetdirect";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "HP Printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.1";

$os = "richoh";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Ricoh Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "sharp";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Sharp Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "sharp";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2385.3.1.";

$os = "okilan";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "OKI Printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "oki";

$os = "brother";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Brother Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "konica";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Konica-Minolta Printer/Copier";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.18334.1.1.1.2.1";

$os = "kyocera";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Kyocera Mita Printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1347.41";

$os = "samsung";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Samsung Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "estudio";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['text']                  = "Toshiba Printer";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "toshiba";
$config['os'][$os]['discovery_os']          = "toshiba";
$config['os'][$os]['over'][0]['graph']      = "device_toner";
$config['os'][$os]['over'][0]['text']       = "Toner";

$os = "sentry3";
$config['os'][$os]['text']                  = "ServerTech Sentry3";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "servertech";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1718.3";
$config['os'][$os]['mibs'][]                = "Sentry3-MIB";

$os = "gude-epc";
$config['os'][$os]['text']                  = "Gude Expert Power Control";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "gude";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.6";
$config['os'][$os]['mibs'][]                = "GUDEADS-EPC8X-MIB";
$config['os'][$os]['mibs'][]                = "GUDEADS-EPC2X6-MIB";

$os = "gude-pdu";
$config['os'][$os]['text']                  = "Gude Expert PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "gude";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.23";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.27";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.35";
$config['os'][$os]['mibs'][]                = "GUDEADS-PDU8110-MIB";
$config['os'][$os]['mibs'][]                = "GUDEADS-PDU8310-MIB";

$os = "geist-pdu";
$config['os'][$os]['text']                  = "Geist PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "geist";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21239.2";
$config['os'][$os]['mibs'][]                = "GEIST-MIB-V3";

$os = "geist-climate";
$config['os'][$os]['text']                  = "Geist Climate Monitor";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "Temperature";
$config['os'][$os]['icon']                  = "geist";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21239.5";
$config['os'][$os]['mibs'][]                = "GEIST-V4-MIB";

$os = "raritan";
$config['os'][$os]['text']                  = "Raritan PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "raritan";
$config['os'][$os]['mibs'][]                = "PDU-MIB";

$os = "mrvld";
$config['os'][$os]['group']                 = "mrv";
$config['os'][$os]['text']                  = "MRV LambdaDriver";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mrv";
$config['os'][$os]['mibs'][]                = "OA-SFP-MIB";
$config['os'][$os]['mibs'][]                = "OADWDM-MIB";

$os = "mrvnbs";
$config['os'][$os]['group']                 = "mrv";
$config['os'][$os]['text']                  = "MRV";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mrv";
$config['os'][$os]['mibs'][]                = "NBS-CMMC-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.629";

$os = "poweralert";
$config['os'][$os]['text']                  = "Tripp Lite PowerAlert";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][0]['text']       = "Current";
$config['os'][$os]['icon']                  = "tripplite";
$config['os'][$os]['mibs'][]                = "UPS-MIB";

$os = "avocent";
$config['os'][$os]['text']                  = "Avocent";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avocent";

$os = "jdsu_edfa";
$config['os'][$os]['text']                  = "JDSU OEM Erbium Dotted Fibre Amplifier";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avocent";
$config['os'][$os]['mibs'][]                = "NSCRTV-ROOT";

$os = "symbol";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['text']                  = "Symbol AP";
$config['os'][$os]['icon']                  = "symbol";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.388";

$os = "firebox";
$config['os'][$os]['text']                  = "Watchguard Firebox";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "watchguard";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['icon']                  = "watchguard";
$config['os'][$os]['mibs'][]                = "WATCHGUARD-SYSTEM-STATISTICS-MIB";

$os = "panos";
$config['os'][$os]['text']                  = "PanOS";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "panos";
//$config['os'][$os]['snmp']['max-rep']       = 50; // PanOS seems to fail here.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";

$os = "arubaos";
$config['os'][$os]['text']                  = "ArubaOS";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "arubaos";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_arubacontroller_numaps";
$config['os'][$os]['over'][0]['text']       = "Number of APs";
$config['os'][$os]['over'][1]['graph']      = "device_arubacontroller_numclients";
$config['os'][$os]['over'][1]['text']       = "Number of Clients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.800.1.1.2.2"; // Rebranded Alcatel

$os = "trapeze";
$config['os'][$os]['text']                  = "Juniper Wireless";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14525.3.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14525.3.1";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['over'][1]['text']       = "Number of Clients";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-CLIENT-SESSION-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-AP-STATUS-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-AP-CONFIG-MIB";

$os = "lcos";
$config['os'][$os]['text']                  = "LANCOM OS";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "lcos";
$config['os'][$os]['mibs'][]                = "LCOS-MIB";

$os = "dsm";
$config['os'][$os]['text']                  = "Synology DSM";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "synology";
$config['os'][$os]['mibs'][]                = "SYNOLOGY-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "SYNOLOGY-DISK-MIB";

$os = "anyusb";
$config['os'][$os]['text']                  = "DIGI OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "digi";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.332.11.6";

// Ubiquiti

$os = "unifi";
$config['os'][$os]['text']                  = "Ubiquiti UniFi";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['icon']                  = "ubiquiti";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['mibs'][]                = "FROGFOOT-RESOURCES-MIB";

$os = "airos";
$config['os'][$os]['text']                  = "Ubiquiti AirOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['icon']                  = "ubiquiti";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['mibs'][]                = "FROGFOOT-RESOURCES-MIB";

$os = "edgeos";
$config['os'][$os]['text']                  = "Ubiquiti EdgeOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "ubiquiti";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30803"; // EdgeOS < 1.5, but overlaps with Vyatta
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.41112"; // EdgeOS >    = 1.5
$config['os'][$os]['mibs'][]                = "UBNT-MIB";

// Draytek firewall/routers

$os = "draytek";
$config['os'][$os]['text']                  = "Draytek";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "draytek";

// SmartEdge OS

$os = "seos";
$config['os'][$os]['text']                  = "SmartEdge OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ericsson";
$config['os'][$os]['mibs'][]                = "RBN-ENVMON-MIB";
$config['os'][$os]['mibs'][]                = "RBN-CPU-METER-MIB";
$config['os'][$os]['mibs'][]                = "RBN-MEMORY-MIB";

// Barracuda NG firewall

$os = "barracudangfw";
$config['os'][$os]['text']                  = "Barracuda NG firewall";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "barracuda";

// Audiocodes

$os = "audiocodes";
$config['os'][$os]['text']                  = "Audiocodes";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['icon']                  = "audiocodes";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5003";
$config['os'][$os]['mibs'][]                = "AC-SYSTEM-MIB";

// Acme Packet

$os = "acme";
$config['os'][$os]['text']                  = "Acme Packet";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "acme";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9148.1";
$config['os'][$os]['mibs'][]                = "ACMEPACKET-ENVMON-MIB";
$config['os'][$os]['mibs'][]                = "APSYSMGMT-MIB";

// HW group

$os = "poseidon";
$config['os'][$os]['text']                  = "Poseidon";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21796.3.3";
$config['os'][$os]['mib_dirs'][]            = "hwgroup";
$config['os'][$os]['mibs'][]                = "POSEIDON-MIB";

$os = "hwg-ste";
$config['os'][$os]['text']                  = "HWg-STE";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][0]['text']       = "temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21796.4.1";
$config['os'][$os]['mib_dirs'][]            = "hwgroup";
$config['os'][$os]['mibs'][]                = "STE-MIB";

$os = "iqnos";
$config['os'][$os]['text']                  = "Infinera IQ";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "infinera";
$config['os'][$os]['mib_dirs'][]            = "infinera";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21296";
$config['os'][$os]['mibs'][]                = "INFINERA-REG-MIB";
$config['os'][$os]['mibs'][]                = "INFINERA-TC-MIB";

$os = "picos";
$config['os'][$os]['text']                  = "Pica8 OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "pica8";

// Radware

$os = "radware";
$config['os'][$os]['text']                  = "Radware DefensePro";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "radware";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.89.1.1.62.16";
$config['os'][$os]['mibs'][]                = "RADWARE-MIB";

// AWind

$os = "wipg";
$config['os'][$os]['text']                  = "WePresent WiPG";
// no type set currently
$config['os'][$os]['icon']                  = "wepresent";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.35251.2.3";

// Patton

$os = "smartware";
$config['os'][$os]['text']                  = "Patton Smartnode";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['icon']                  = "patton";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1768.100.4.";
$config['os'][$os]['mibs'][]                = "SMARTNODE-MIB";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Load";

// Riverbed

$os = "steelhead";
$config['os'][$os]['text']                  = "Riverbed Steelhead";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "riverbed";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.17163.1.1"; // Steelhead
$config['os'][$os]['mibs'][]                = "STEELHEAD-MIB";

// Opengear

$os = "opengear";
$config['os'][$os]['text']                  = "Opengear";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "opengear";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.81";
// MIBs disabled until not implemented
#$config['os'][$os]['mibs'][]              = "OG-CONNECT-MIB";
#$config['os'][$os]['mibs'][]              = "OG-DATA-MIB";
#$config['os'][$os]['mibs'][]              = "OG-FAILOVER-MIB";
#$config['os'][$os]['mibs'][]              = "OG-HOST-MIB";
#$config['os'][$os]['mibs'][]              = "OG-PATTERN-MIB";
#$config['os'][$os]['mibs'][]              = "OG-PRODUCTS-MIB";
#$config['os'][$os]['mibs'][]              = "OG-SENSOR-MIB";
#$config['os'][$os]['mibs'][]              = "OG-SIGNAL-MIB";
#$config['os'][$os]['mibs'][]              = "OG-SMI-MIB";
#$config['os'][$os]['mibs'][]              = "OG-STATUS-MIB";
#$config['os'][$os]['mibs'][]              = "OG-STATUSv2-MIB";
#$config['os'][$os]['mibs'][]              = "OG-UPS-MIB";
#$config['os'][$os]['mibs'][]              = "OGTRAP-MIB";
#$config['os'][$os]['mibs'][]              = "OGTRAPv2-MIB";

$os = "zeustm";
$config['os'][$os]['text']                  = "Riverbed Stingray";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "riverbed";
$config['os'][$os]['discovery_os']          = "linux";
//$config['os'][$os]['mibs'][]                = "ZXTM-MIB";       // Not implemented
//$config['os'][$os]['mibs'][]                = "ZXTM-MIB-SMIv2"; // Not implemented

foreach ($config['os'] as $this_os => $blah)
{
  if (isset($config['os'][$this_os]['group']))
  {
    $this_os_group = $config['os'][$this_os]['group'];
    if (isset($config['os_group'][$this_os_group]))
    {
      foreach ($config['os_group'][$this_os_group] as $property => $value)
      {
        if (!isset($config['os'][$this_os][$property]))
        {
          $config['os'][$this_os][$property] = $value;
        }
      }
    }
  }
}

// EOF
