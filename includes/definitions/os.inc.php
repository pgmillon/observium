<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2015 Adam Armstrong
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
 * BACKEND:
 * $config['os'][$os]['vendor']               (string) vendor name for this os/group
 * $config['os'][$os]['group']                (string) sets os_group for this os
 * $config['os'][$os]['type']                 (string) sets type for this os. Must be one of specified in $config['device_types']
 * $config['os'][$os]['discovery_blacklist']  (array)  blacklist discovery modules
 * $config['os'][$os]['ifAliasSemicolon']     (bool)   split ifAlias on semicolon and take the first element.
 * $config['os'][$os]['ifAlias_ifDescr']      (bool)   copies ifDescr to ifAlias if ifAlias isn't the same as ifName
 * $config['os'][$os]['mibs']                 (array)  list of supported MIBs
 * $config['os'][$os]['mib_dirs']             (array)  default MIB directories used when making snmp requests
 * $config['os'][$os]['mib_blacklist']        (array)  list of blacklisted MIBs (exclude default and group mibs)
 * $config['os'][$os]['nobulk']               (bool)   turn off bulkwalk if the OS does not support it correctly
 * $config['os'][$os]['snmp']['max-rep']      (int)    sets maximum repetitions in SNMP getbulk PDU
 * $config['os'][$os]['sysObjectID']          (array)  list of sysObjectIDs matching this device, for OS discovery.
 * $config['os'][$os]['sysDescr']             (array)  regexp list of sysDescr matching this device, for OS discovery.
 * $config['os'][$os]['discovery_os']         (string) this is used only for detect OS in get_device_os().
 *                                             Used when OS discovered by filename does not match includes/discovery/os/$os.inc.php
 *
 * WEB:
 * $config['os'][$os]['text']                 (string) is OS name displayed on web pages
 * $config['os'][$os]['icon']                 (string) icon name displayed for os
 * $config['os'][$os]['icons']                (array)  list if possible alternative icons, selectable in the web interface or settable by code
 * $config['os'][$os]['over']                 (array)  this is displaying options for a web pages;
 * |-> $config['os'][$os]['over'][x]['graph'] (string) sets the graph type.
 * \-> $config['os'][$os]['over'][x]['text']  (string) optionally overrides graph title; is normally taken from graph definitions.
 * $config['os'][$os]['processor_stacked']    (bool)   use stacked processor graph
 * $config['os'][$os]['realtime']             (int)    default interval setting (in seconds) for the realtime graph page
 * $config['os'][$os]['ifname']               (bool)   use ifName instead of ifDescr as a port name
 * $config['os'][$os]['comments']             (string) Regexp! Here regular expression for ignore device comments on show device config page
 *
 */

$config['os']['default']['over'][0]['graph']        = "device_bits";
$config['os']['default']['over'][1]['graph']        = "device_uptime";
$config['os']['default']['over'][2]['graph']        = "device_ping";
$config['os']['default']['comments']                = "/^\s*#/";
// MIBs enabled for any os (except blacklisted mibs)
$config['os']['default']['mibs'][]                  = "ENTITY-MIB";
$config['os']['default']['mibs'][]                  = "ENTITY-SENSOR-MIB";
$config['os']['default']['mibs'][]                  = "UCD-SNMP-MIB";   // Should be before HOST-RESOURCES-MIB (in storage)
$config['os']['default']['mibs'][]                  = "HOST-RESOURCES-MIB";
$config['os']['default']['mibs'][]                  = "Q-BRIDGE-MIB";
$config['os']['default']['mibs'][]                  = "LLDP-MIB";       // Should be before CISCO-CDP-MIB, but I not know why (in discovery-protocols)
$config['os']['default']['mibs'][]                  = "CISCO-CDP-MIB";
$config['os']['default']['mibs'][]                  = "PW-STD-MIB";     // Pseudowires. FIXME, possible more os specific?
$config['os']['default']['mibs'][]                  = "BGP4-MIB";

// Group definitions

$os_group = "unix";
$config['os_group'][$os_group]['type']              = "server";
$config['os_group'][$os_group]['processor_stacked'] = 1;
$config['os_group'][$os_group]['over'][0]['graph']  = "device_processor";
$config['os_group'][$os_group]['over'][1]['graph']  = "device_ucd_memory";
//$config['os_group'][$os_group]['mibs'][]            = "UCD-SNMP-MIB"; // In default now
$config['os_group'][$os_group]['mibs'][]            = "LSI-MegaRAID-SAS-MIB";

$os_group = "printer";
$config['os_group'][$os_group]['type']              = "printer";
$config['os_group'][$os_group]['over'][0]['graph']  = "device_toner";
$config['os_group'][$os_group]['mibs'][]            = "Printer-MIB";
$config['os_group'][$os_group]['mib_blacklist'][]   = "CISCO-CDP-MIB";
$config['os_group'][$os_group]['mib_blacklist'][]   = "PW-STD-MIB";
$config['os_group'][$os_group]['mib_blacklist'][]   = "BGP4-MIB";

$os_group = "cisco";
$config['os_group'][$os_group]['vendor']            = "Cisco";
$config['os_group'][$os_group]['type']              = "network";
$config['os_group'][$os_group]['over'][0]['graph']  = "device_bits";
$config['os_group'][$os_group]['over'][1]['graph']  = "device_processor";
$config['os_group'][$os_group]['over'][2]['graph']  = "device_mempool";
$config['os_group'][$os_group]['comments']          = "/^\s*!/";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-SENSOR-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-VTP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENVMON-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-QFP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IP-STAT-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IPSEC-FLOW-MONITOR-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-REMOTE-ACCESS-MONITOR-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-FIREWALL-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-VPDN-MGMT-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENHANCED-MEMPOOL-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-MEMORY-POOL-MIB"; // Keep this below CISCO-ENHANCED-MEMPOOL-MIB, checks for duplicates.
$config['os_group'][$os_group]['mibs'][]            = "CISCO-PROCESS-MIB";     // Goes after "CISCO-MEMORY-POOL-MIB" and "CISCO-ENHANCED-MEMPOOL-MIB" cos Cisco suck.
$config['os_group'][$os_group]['mibs'][]            = "CISCO-EIGRP-MIB";       // FIXME. Seems this MIB supported only in IOS Catalyst. See ftp://ftp.cisco.com/pub/mibs/supportlists/
$config['os_group'][$os_group]['mibs'][]            = "CISCO-CEF-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IETF-IP-MIB";     // IPv6 addresses
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IETF-PW-MIB";     // Pseudowires
$config['os_group'][$os_group]['mibs'][]            = "CISCO-BGP4-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-RTTMON-MIB";      // SLA
$config['os_group'][$os_group]['mib_blacklist'][]   = "PW-STD-MIB";            // Exclude standart pseudowires

// Generic (unknown) device OS
$os = "generic";
$config['os'][$os]['text']                  = "Generic Device";

// Linux-based OSes here please.

$os = "linux";
$config['os'][$os]['text']                  = "Linux";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";
$config['os'][$os]['mibs'][]                = "SUPERMICRO-HEALTH-MIB";
$config['os'][$os]['mibs'][]                = "MIB-Dell-10892";
$config['os'][$os]['mibs'][]                = "CPQHLTH-MIB";
$config['os'][$os]['mibs'][]                = "CPQIDA-MIB";
$config['os'][$os]['realtime']              = 15;

$os = "vmware";
$config['os'][$os]['text']                  = "VMware";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = '.1.3.6.1.4.1.6876.4.1';
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";
//$config['os'][$os]['mib_blacklist'][]       = "BGP4-MIB";

$os = "qnap";
$config['os'][$os]['text']                  = "QNAP TurboNAS";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['realtime']              = 15;

$os = "dss";
$config['os'][$os]['text']                  = "Open-E DSS";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['icon']                  = "open-e";
$config['os'][$os]['realtime']              = 15;

$os = "vyatta";
$config['os'][$os]['text']                  = "Vyatta Core";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30803";
$config['os'][$os]['sysDescr'][]            = "/^Vyatta (?!VyOS)/";

$os = "vyos";
$config['os'][$os]['text']                  = "VyOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.44641";
$config['os'][$os]['sysDescr'][]            = "/^(Vyatta )*VyOS/";

$os = "endian";
$config['os'][$os]['text']                  = "Endian";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";

$os = "openwrt";
$config['os'][$os]['text']                  = "OpenWrt";
$config['os'][$os]['type']                  = "network"; /// Or wireless, or firewall?
//$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";

$os = "ddwrt";
$config['os'][$os]['text']                  = "DD-WRT";
$config['os'][$os]['type']                  = "network"; /// Or wireless, or firewall?
//$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";

$os = "wut";
$config['os'][$os]['text']                  = "Web-Thermograph";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5040.1";
$config['os'][$os]['mibs'][]                = "WebGraph-8xThermometer-US-MIB";

$os = "terastation";
$config['os'][$os]['text']                  = "BUFFALO TeraStation";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "buffalo";
$config['os'][$os]['sysDescr'][]            = "/^BUFFALO TeraStation/";

// Fireeye
$os = "fireeye";
$config['os'][$os]['text']                  = "Fireeye";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "Processors";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
$config['os'][$os]['mib_dirs'][]            = "fireeye";
$config['os'][$os]['mibs'][]                = "FE-FIREEYE-MIB";

// Check Point

$os = "ipso";
$config['os'][$os]['text']                  = "Check Point IPSO"; // Old vendor NOKIA
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.94.1.21.2.1";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";
$config['os'][$os]['mibs'][]                = "NOKIA-IPSO-SYSTEM-MIB";

$os = "sofaware";
$config['os'][$os]['text']                  = "Check Point Embedded NGX";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6983.1";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";
$config['os'][$os]['mibs'][]                = "EMBEDDED-NGX-MIB";

$os = "infoblox";
$config['os'][$os]['text']                  = "Infoblox";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "infoblox";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.7779.1";
$config['os'][$os]['mibs'][]                = "IB-DNSONE-MIB";
$config['os'][$os]['mibs'][]                = "IB-DHCPONE-MIB";
$config['os'][$os]['mibs'][]                = "IB-PLATFORMONE-MIB";

$os = "splat";
$config['os'][$os]['text']                  = "Check Point SecurePlatform";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";

$os = "gaia";
$config['os'][$os]['text']                  = "Check Point GAiA";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "checkpoint";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['mibs'][]                = "CHECKPOINT-MIB";

$os = "infratec-rms";
$config['os'][$os]['text']                  = "Infratec RMS";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1909.10";
$config['os'][$os]['mibs'][]                = "INFRATEC-RMS-MIB";

$os = "sensatronics";
$config['os'][$os]['text']                  = "Sensatronics";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "sensatronics";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.16174.1";

// Other Unix-based OSes here please.

$os = "ibmi";
$config['os'][$os]['text']                  = "IBM System i";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.6.11";

$os = "freebsd";
$config['os'][$os]['text']                  = "FreeBSD";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.8";

$os = "openbsd";
$config['os'][$os]['text']                  = "OpenBSD";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30155.23.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.12"; // Net-SNMP
$config['os'][$os]['sysDescr'][]            = "/^OpenBSD/";
$config['os'][$os]['mibs'][]                = "OPENBSD-SENSORS-MIB";

$os = "netbsd";
$config['os'][$os]['text']                  = "NetBSD";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysDescr'][]            = "/^NetBSD/";

$os = "dragonfly"; // FIXME. Not have any sysDescr/sysObjectID or file for detect os
$config['os'][$os]['text']                  = "DragonflyBSD";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";

$os = "netware";
$config['os'][$os]['text']                  = "Novell Netware";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['icon']                  = "novell";
$config['os'][$os]['sysDescr'][]            = "/Novell NetWare/";

$os = "darwin";
$config['os'][$os]['text']                  = "Mac OS X";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysDescr'][]            = "/Darwin Kernel Version/";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";

$os = "monowall";
$config['os'][$os]['text']                  = "m0n0wall";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";

$os = "pfsense";
$config['os'][$os]['text']                  = "pfSense";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";

$os = "freenas";
$config['os'][$os]['text']                  = "FreeNAS";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";

$os = "nas4free";
$config['os'][$os]['text']                  = "NAS4Free";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";

$os = "solaris";
$config['os'][$os]['text']                  = "Sun Solaris";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.42.2.1.1";

$os = "opensolaris";
$config['os'][$os]['text']                  = "Sun OpenSolaris";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";

$os = "openindiana";
$config['os'][$os]['text']                  = "OpenIndiana";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";

$os = "nexenta";
$config['os'][$os]['text']                  = "NexentaOS";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "solaris";

$os = "nestos";
$config['os'][$os]['text']                  = "Nexsan NST";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "nexsan";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.7247.1.1";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";

$os = "aix";
$config['os'][$os]['text']                  = "AIX";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['ifAliasSemicolon']      = TRUE;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.3.1.2.1.1.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2.3.1.2.1.1.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.15";
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

$os = "adva";
$config['os'][$os]['text']                  = "Adva Optical";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1671";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2544";
$config['os'][$os]['mib_dirs'][]            = "adva";
$config['os'][$os]['mibs'][]                = "FspR7-MIB";
//$config['os'][$os]['mibs'][]                = "ADVA-MIB";

$os = "equallogic";
$config['os'][$os]['text']                  = "Storage Array Firmware";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12740.17.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12740.12.1.1.0";
$config['os'][$os]['mibs'][]                = "EQLMEMBER-MIB";
$config['os'][$os]['mibs'][]                = "EQLDISK-MIB";
$config['os'][$os]['mib_blacklist'][]       = "BGP4-MIB";

// AdTran

$os = "adtran-aos";
$config['os'][$os]['text']                  = "ADTRAN AOS";
$config['os'][$os]['group']                 = "adtran-aos";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['icon']                  = "adtran";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.664.1";
$config['os'][$os]['mib_dirs'][]            = "adtran";
$config['os'][$os]['mibs'][]                = "ADTRAN-AOSCPU";

// Alcatel

$os = "aos";
$config['os'][$os]['text']                  = "Alcatel-Lucent OS";
$config['os'][$os]['group']                 = "aos";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['icon']                  = "alcatellucent";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-HEALTH-MIB";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "ALCATEL-IND1-INTERSWITCH-PROTOCOL-MIB";
$config['os'][$os]['mib_blacklist'][]       = "CISCO-CDP-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.800.1.1.2.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.800.1.1.2.2.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.801.1.1.2.1";

$os = "timos";
$config['os'][$os]['text']                  = "Alcatel-Lucent TimOS";
$config['os'][$os]['group']                 = "timos";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['icon']                  = "alcatellucent";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6527.";
$config['os'][$os]['mib_dirs'][]            = "aos";
$config['os'][$os]['mibs'][]                = "TIMETRA-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "TIMETRA-CHASSIS-MIB";

// Cisco

$os = "iosxr";
$config['os'][$os]['text']                  = "Cisco IOS-XR";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysDescr'][]            = "/IOS XR/";
$config['os'][$os]['icon']                  = "cisco";
//$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['realtime']              = 30; // Yes it's really minimal interval when counters changed in IOS-XR
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['mib_blacklist'][]       = "CISCO-EIGRP-MIB"; // Not supported, timeout
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

$os = "iosxe";
$config['os'][$os]['text']                  = "Cisco IOS-XE";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysDescr'][]            = "/IOS-XE/";
#$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['realtime']              = 10;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['mibs'][]                = "CISCO-CONFIG-MAN-MIB";
$config['os'][$os]['mib_blacklist'][]       = "CISCO-EIGRP-MIB"; // Not supported, timeout
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

$os = "ios";
$config['os'][$os]['text']                  = "Cisco IOS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysDescr'][]            = "/Cisco (IOS|Internetwork Operating System) Software/";
$config['os'][$os]['sysDescr'][]            = "/IOS \(tm\)/";
$config['os'][$os]['sysDescr'][]            = "/Global Site Selector/";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['realtime']              = 10;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['mibs'][]                = "CISCO-CONFIG-MAN-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-CAT6K-CROSSBAR-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-DOT11-ASSOCIATION-MIB";
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

$os = "acsw";
$config['os'][$os]['text']                  = "Cisco ACE";
#$config['os'][$os]['group']                = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.729";  // ACE 4G in Cat6500
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.730";  // ACE in Cat6500
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.824";  // ACE 4710
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1231"; // ACE in Cat6500
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1291"; // ACE in Cat6500
$config['os'][$os]['sysDescr'][]            = "/^ACE /";
$config['os'][$os]['sysDescr'][]            = "/(Cisco )?Application Control (Software|Engine)/";
$config['os'][$os]['mibs'][]                = "CISCO-PROCESS-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-SLB-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-ENHANCED-SLB-MIB";

$os = "asa";
$config['os'][$os]['text']                  = "Cisco ASA";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['sysDescr'][]            = "/Cisco Adaptive Security Appliance/";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";

$os = "fwsm";
$config['os'][$os]['text']                  = "Cisco Firewall Service Module";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['sysDescr'][]            = "/Cisco Firewall Services Module/";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";

$os = "pixos";
$config['os'][$os]['text']                  = "Cisco PIX-OS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['sysDescr'][]            = "/Cisco PIX/";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";

$os = "nxos";
$config['os'][$os]['text']                  = "Cisco NX-OS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
//$config['os'][$os]['snmp']['max-rep']       = 100; // issues apparent
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysDescr'][]            = "/NX-OS/";

$os = "sanos";
$config['os'][$os]['text']                  = "Cisco SAN-OS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysDescr'][]            = "/SAN-OS/";

$os = "catos";
$config['os'][$os]['text']                  = "Cisco CatOS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysDescr'][]            = "/Cisco (Catalyst Operating System Software|Systems Catalyst 1900)/";
$config['os'][$os]['icon']                  = "cisco-old";
$config['os'][$os]['snmp']['max-rep']       = 20;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";

$os = "wlc";
$config['os'][$os]['text']                  = "Cisco WLC";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.828";  // 2100 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.926";  // 500 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1069"; // 5500 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1279"; // 2504
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1293"; // WiSM-2
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1295"; // 7500 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1615"; // 8500 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1631"; // Virtual
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1645"; // 5760 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.2026"; // 5760 Series
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14179";
$config['os'][$os]['sysDescr'][]            = "/^Cisco Controller$/";
$config['os'][$os]['mib_dirs'][]            = "cisco";
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";
$config['os'][$os]['mibs'][]                = "AIRESPACE-WIRELESS-MIB";
$config['os'][$os]['mibs'][]                = "AIRESPACE-SWITCHING-MIB";
$config['os'][$os]['mibs'][]                = "CISCO-LWAPP-SYS-MIB";

$os = "cisco-ons";
$config['os'][$os]['text']                  = "Cisco Cerent ONS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3607.";
// MIBs disabled until not implemented
//$config['os'][$os]['mibs'][]                = "CERENT-ENVMON-MIB";
//$config['os'][$os]['mibs'][]                = "CERENT-OPTICAL-MONITOR-MIB";

$os = "cisco-acs";
$config['os'][$os]['text']                  = "Cisco Secure ACS";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysDescr'][]            = "/Cisco Secure (ACS|Access Control System)/";

$os = "cisco-lms";
$config['os'][$os]['text']                  = "Cisco Prime LMS";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.10.56"; // This sysObjectId intersects with Cisco ACS

$os = "ciscosmblinux";
$config['os'][$os]['text']                  = "Cisco SMB Linux";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";

$os = "cisco-ade";
$config['os'][$os]['text']                  = "Cisco ADE";
$config['os'][$os]['vendor']                = "cisco";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysDescr'][]            = "/Cisco Application Deployment Engine/";

$os = "meraki";
$config['os'][$os]['text']                  = "Cisco Meraki";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
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
$config['os'][$os]['over'][1]['graph']      = "device_power";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1512"; // C200
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1513"; // C210
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1514"; // C250
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1515"; // C260
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1516"; // C460
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1682";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1683";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1684";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1685";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1864"; // E140S
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.1931"; // EN120S
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.2178"; // C220
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.2179"; // C240
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.1.2180"; // C3160
$config['os'][$os]['mibs'][]                = "CISCO-UCS-CIMC-MIB";

// Cisco IronPort

$os = "asyncos";
$config['os'][$os]['text']                  = "Cisco IronPort";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['sysDescr'][]            = "/IronPort.* AsyncOS/";
$config['os'][$os]['mibs'][]                = "ASYNCOS-MAIL-MIB";

// Cisco Small Business (Linksys)

$os = "ciscosb";
#$config['os'][$os]['group']                 = "cisco"; // Cisco SB is not Cisco! --mike
$config['os'][$os]['text']                  = "Cisco Small Business";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ciscosb";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.80.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.81.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.82.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.83.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.85.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.87."; // SF200-48
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.88."; // SG200-50
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.89.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9.6.1.11.82.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3955.";
$config['os'][$os]['mibs'][]                = "CISCOSB-rndMng";

// Cisco Service Control OS / SCE

$os = "ciscoscos";
$config['os'][$os]['text']                  = "Cisco Service Control OS";
$config['os'][$os]['group']                 = "cisco";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysDescr'][]            = "/Cisco Service Control/";
$config['os'][$os]['icon']                  = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";

// Huawei

$os = "vrp";
$config['os'][$os]['text']                  = "Huawei VRP";
$config['os'][$os]['group']                 = "vrp";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "huawei";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.1.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.6.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.10.";
$config['os'][$os]['sysDescr'][]            = "/Huawei(-3Com)? Versatile Routing Platform Software/";
$config['os'][$os]['mibs'][]                = "HUAWEI-ENTITY-EXTENT-MIB";

$os = "huawei-vsp";
$config['os'][$os]['text']                  = "Huawei VSP";
$config['os'][$os]['group']                 = "vrp";
$config['os'][$os]['type']                  = "security";
$config['os'][$os]['icon']                  = "huawei";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.159";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.122";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.125";
$config['os'][$os]['sysDescr'][]            = "/Huawei Versatile Security Platform Software/";
$config['os'][$os]['mibs'][]                = "HUAWEI-ENTITY-EXTENT-MIB";

$os = "huawei-ias";
$config['os'][$os]['text']                  = "Huawei IAS";
$config['os'][$os]['group']                 = "ias";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "huawei";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.109"; // MA5606T
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.115"; // MA5680T
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.128";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.132"; // MA5626E
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.133"; // MA5683T
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.134"; // MA5620G
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.167"; // MA5610
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.2.169"; // MA5616
$config['os'][$os]['sysDescr'][]            = "/Huawei Integrated Access Software/";
$config['os'][$os]['mibs'][]                = "HUAWEI-ENTITY-EXTENT-MIB";

// ZTE

$os = "zxr10";
$config['os'][$os]['text']                  = "ZTE ZXR10";
$config['os'][$os]['group']                 = "zxr10";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zte";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysDescr'][]            = "/^(ZXR10|ZTE ZXR10)/";

// Netgear

$os = "netgear";
$config['os'][$os]['text']                  = "Netgear";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "netgear";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4526";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12622";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['mibs'][]                = "UCD-SNMP-MIB";

// Korenix

$os = "korenix-jetnet";
$config['os'][$os]['text']                  = "Korenix Jetnet";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "korenix";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.24062.2.3";

// Supermicro Switch

$os = "supermicro-switch";
$config['os'][$os]['text']                  = "Supermicro Switch";
$config['os'][$os]['group']                 = "supermicro";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "supermicro";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysDescr'][]            = "/^Supermicro Switch/";
$config['os'][$os]['sysDescr'][]            = "/^(SSE|SBM)-/";

// Juniper

$os = "junos";
$config['os'][$os]['text']                  = "Juniper JunOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
// $config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
//$config['os'][$os]['discovery_blacklist'][] = "entity-sensor";
//$config['os'][$os]['discovery_blacklist'][] = "entity-physical";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2636";
$config['os'][$os]['mib_dirs'][]            = "junos";
$config['os'][$os]['mibs'][]                = "JUNIPER-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-ALARM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-DOM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-SRX5000-SPU-MONITORING-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-VLAN-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-MAC-MIB";
$config['os'][$os]['mibs'][]                = "BGP4-V2-MIB-JUNIPER";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-MIB";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-SENSOR-MIB";

$os = "junose";
$config['os'][$os]['text']                  = "Juniper JunOSe";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
//$config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4874";
$config['os'][$os]['mib_dirs'][]            = "junose";
$config['os'][$os]['mibs'][]                = "JUNIPER-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-DOM-MIB";
$config['os'][$os]['mibs'][]                = "Juniper-System-MIB";
$config['os'][$os]['mibs'][]                = "BGP4-V2-MIB-JUNIPER";

$os = "jwos";
$config['os'][$os]['text']                  = "Juniper JWOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8239.1.2.9";

$os = "screenos";
$config['os'][$os]['text']                  = "Juniper ScreenOS";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.3224.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3224";
$config['os'][$os]['mibs'][]                = "NETSCREEN-RESOURCE-MIB";

$os = "juniperive";
$config['os'][$os]['text']                  = "Juniper IVE";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12532";
$config['os'][$os]['mibs'][]                = "JUNIPER-IVE-MIB";

// Fortinet

$os = "fortigate";
$config['os'][$os]['text']                  = "Fortinet Fortigate";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "fortinet";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_fortigate_cpu";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
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
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
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
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14988";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14988.1"; // Routers
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14988.2"; // SOHO swithes
$config['os'][$os]['mib_dirs'][]            = "mikrotik";
$config['os'][$os]['mibs'][]                = "MIKROTIK-MIB";

// Brocade / Foundry

$os = "ironware";
$config['os'][$os]['text']                  = "Brocade FastIron/IronWare";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['snmp']['max-rep']       = 60;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.1"; // FastIron Workgroup Switch
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.5"; // EdgeIron
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.16";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-SWITCH-GROUP-MIB";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-AGENT-MIB";
$config['os'][$os]['mib_blacklist'][]       = "CISCO-CDP-MIB";

$os = "ironware-ap";
$config['os'][$os]['text']                  = "Brocade AP";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "brocade";
//$config['os'][$os]['snmp']['max-rep']       = 60;
//$config['os'][$os]['over'][0]['graph']      = "device_bits";
//$config['os'][$os]['over'][1]['graph']      = "device_processor";
//$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.6";  // Foundry AP
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1991.1.15";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-SWITCH-GROUP-MIB";
$config['os'][$os]['mibs'][]                = "FOUNDRY-SN-AGENT-MIB";
$config['os'][$os]['mib_blacklist'][]       = "CISCO-CDP-MIB";

$os = "fabos";
$config['os'][$os]['text']                  = "Brocade FabricOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1588.2.1.1";
$config['os'][$os]['mibs'][]                = "SW-MIB";

$os = "nos";
$config['os'][$os]['text']                  = "Brocade NOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "brocade";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['ifDescr_ifAlias']       = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1588.2.2";
$config['os'][$os]['sysDescr'][]            = "/Brocade VDX/";
$config['os'][$os]['mibs'][]                = "SW-MIB";

// Extreme Networks

$os = "xos";
$config['os'][$os]['text']                  = "Extreme XOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['group']                 = "extremeware";
$config['os'][$os]['discovery_os']          = "extremeware";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['icon']                  = "extreme";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
#$config['os'][$os]['over'][1]['graph']      = "device_processor";
#$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['mibs'][]                = "EXTREME-BASE-MIB";

$os = "extremeware";
$config['os'][$os]['text']                  = "Extremeware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "extreme";
$config['os'][$os]['discovery_os']          = "extremeware";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['mibs'][]                = "EXTREME-BASE-MIB"; // Probably?

// Enterasys / Extreme Networks since 2013

$os = "enterasys";
$config['os'][$os]['text']                  = "Enterasys";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "enterasys";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5624.2.1.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5624.2.2.";

$os = "enterasys-wl";
$config['os'][$os]['text']                  = "Extreme Wireless Controller";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "enterasys";
#$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.4329.15.1.1";

// Bluecoat

$os = "packetshaper";
$config['os'][$os]['text']                  = "Blue Coat Packetshaper";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "bluecoat";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2334.";

$os = "proxyav";
$config['os'][$os]['text']                  = "Blue Coat Proxy AV";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][0]['text']       = "Traffic";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['text']       = "CPU Usage";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['text']       = "Memory";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.2.11"; //FIXME, duplicate with proxysg
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.1.1";
$config['os'][$os]['sysDescr'][]            = "/ProxyAV/";
$config['os'][$os]['mibs'][]                = "BLUECOAT-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-AV-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-SG-USAGE-MIB";

$os = "proxysg";
$config['os'][$os]['text']                  = "Blue Coat Proxy SG";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "bluecoat";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.2.11";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3417.1.1";
$config['os'][$os]['sysDescr'][]            = "/SGOS/";
$config['os'][$os]['mibs'][]                = "BLUECOAT-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-SG-PROXY-MIB";
$config['os'][$os]['mibs'][]                = "BLUECOAT-SG-SENSOR-MIB";
// $config['os'][$os]['mibs'][]                = "BLUECOAT-SG-ICAP-MIB";

// Zhone

$os = "zhonedslam";
$config['os'][$os]['text']                  = "Zhone DLSAM";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "zhone";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1795";

// A10

$os = "a10-ax";
$config['os'][$os]['text']                  = "A10 ACOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "a10";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.22610.1.3";
$config['os'][$os]['mibs'][]                = "A10-AX-MIB";


// Avaya

$os = "avaya-ers";
$config['os'][$os]['text']                  = "ERS Firmware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avaya";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.45.3";
$config['os'][$os]['mibs'][]                = "S5-CHASSIS-MIB";

// Arista

$os = "arista_eos";
$config['os'][$os]['text']                  = "Arista EOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "arista";
//$config['os'][$os]['snmp']['max-rep']       = 100; // Seems to break.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30065.1.3011.7124";
$config['os'][$os]['sysDescr'][]            = "/^Arista Networks EOS/";
$config['os'][$os]['mibs'][]                = "ARISTA-ENTITY-SENSOR-MIB";

// Calix

$os = "calix";
$config['os'][$os]['text']                  = "Calix";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "calix";
#$config['os'][$os]['snmp']['max-rep']       = 15; // More - breaks, less or nobulk - very slow polling and discovery
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6321";
$config['os'][$os]['mibs'][]                = "E7-Calix-MIB";

// Citrix

$os = "netscaler";
$config['os'][$os]['text']                  = "Citrix Netscaler";
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "citrix";
//$config['os'][$os]['snmp']['max-rep']       = 50; // Seems to break
$config['os'][$os]['over'][0]['graph']      = "device_netscaler_tcp_conn";
$config['os'][$os]['over'][1]['graph']      = "device_bits";
$config['os'][$os]['over'][2]['graph']      = "device_processor";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5951.1";
$config['os'][$os]['mibs'][]                = "NS-ROOT-MIB";

// F5

$os = "f5";
$config['os'][$os]['text']                  = "F5 BIG-IP";
$config['os'][$os]['type']                  = "loadbalancer";
$config['os'][$os]['icon']                  = "f5";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
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
$config['os'][$os]['over'][1]['graph']      = "device_temperature";
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
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";

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
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11898.2.4.9";

// Ruckus Wireless <http://www.ruckuswireless.com>

$os = "ruckus-zf";                          // Ruckus ZoneFlex
$config['os'][$os]['text']                  = "Ruckus ZoneFlex";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "ruckus";
// $config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25053.3.1.4";
$config['os'][$os]['mibs'][]                = "RUCKUS-RADIO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-WLAN-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-SWINFO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-HWINFO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-DEVICE-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-SYSTEM-MIB";

$os = "ruckus-zd";                          // Ruckus ZoneDirector
$config['os'][$os]['text']                  = "Ruckus ZoneDirector";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "ruckus";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25053.3.1.5";
$config['os'][$os]['mibs'][]                = "RUCKUS-RADIO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-WLAN-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-SWINFO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-HWINFO-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-DEVICE-MIB";
$config['os'][$os]['mibs'][]                = "RUCKUS-SYSTEM-MIB";

// Trango

$os = "trango-apex";
$config['os'][$os]['text']                  = "Trango Apex";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "trango";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5454.1.60";
$config['os'][$os]['mibs'][]                = "TRANGO-APEX-RF-MIB";
$config['os'][$os]['mibs'][]                = "TRANGO-APEX-GIGE-MIB";
$config['os'][$os]['mibs'][]                = "TRANGO-APEX-MODEM-MIB";
$config['os'][$os]['mibs'][]                = "TRANGO-APEX-SYS-MIB";
$config['os'][$os]['over'][0]['graph']      = "device_dbm";
$config['os'][$os]['over'][1]['graph']      = "device_temperature";
$config['os'][$os]['over'][2]['graph']      = "device_ping";

// Dell

// Dell Force 10. FTOS is now called DNOS v9 and runs on mid/high-end dell switches.

$os = "ftos";
$config['os'][$os]['text']                  = "Force10 FTOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "force10";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['mib_dirs'][]            = "force10";
$config['os'][$os]['mibs'][]                = "F10-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "F10-C-SERIES-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "F10-S-SERIES-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "F10-M-SERIES-CHASSIS-MIB";
$config['os'][$os]['mibs'][]                = "FORCE10-BGP4-V2-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.1"; // f10ESeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.2"; // f10CSeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.3"; // f10SSeriesProducts
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6027.1.4"; // f10MSeriesProducts


/// This is only to be used for Dell Network Operating System (DNOS) v6 Devices.
/// This is just a renaming of Dell PowerConnect

$os = 'dnos6';
$config['os'][$os]['text']                  = 'Dell Networking OS';
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = 'network';
$config['os'][$os]['icon']                  = 'dell';
$config['os'][$os]['over'][0]['graph']      = 'device_bits';
$config['os'][$os]['over'][1]['graph']      = 'device_processor';
$config['os'][$os]['over'][2]['graph']      = 'device_mempool';
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3042";  // N4032
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3044";  // N4032F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3045";  // N4064
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3046";  // N4064F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3053";  // N2024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3054";  // N2048
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
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3006";  // 3424
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3007";  // 3448
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3008";  // 3424P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3009";  // 3448P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3010";  // 6224
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3011";  // 6248
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3012";  // 6224P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3013";  // 6248P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3014";  // 6224F
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3015";  // M6220
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3022";  // M8024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3023";  // 8024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3024";  // 8024F
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
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3000"; // 6024
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3003"; // 3348
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3004"; // 5324
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3005"; // 5316
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3016"; // 3534
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3017"; // 3548
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3018"; // 3524P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3019"; // 3548P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3020"; // 5424
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3021"; // 5448
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3028"; // 2824
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3029"; // 2848
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3030"; // 5524
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3031"; // 5548
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3032"; // 5524P
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10895.3033"; // 5548P
$config['os'][$os]['mibs'][]                = "RADLAN-HWENVIROMENT";
//$config['os'][$os]['mibs'][]                = "Dell-Vendor-MIB"; // Keep this below RADLAN-HWENVIROMENT, checks for duplicate sensors
$config['os'][$os]['mibs'][]                = "RADLAN-rndMng";

$os = "powervault";
$config['os'][$os]['text']                  = "Dell PowerVault";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10893.2.102";

$os = "drac";
$config['os'][$os]['text']                  = "Dell iDRAC";
$config['os'][$os]['type']                  = "management";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.5";
$config['os'][$os]['mib_dirs'][]            = "dell";
$config['os'][$os]['mibs'][]                = "DELL-RAC-MIB";

$os = "sonicwall";
$config['os'][$os]['text']                  = "SonicWALL";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.1"; // Firewall
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.3"; // Global Management System
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.4"; // Email Security
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.5"; // Datacenter Operations
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.6"; // SSL VPN
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8741.7"; // CDP
$config['os'][$os]['mib_dirs'][]            = "sonicwall";
$config['os'][$os]['mibs'][]                = "SONICWALL-FIREWALL-IP-STATISTICS-MIB";

// Arbor Networks

$os = "arbos";
$config['os'][$os]['text']                  = "ArbOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "arbor";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.9694";
$config['os'][$os]['mib_dirs'][]            = "arbor";
$config['os'][$os]['mibs'][]                = "PEAKFLOW-SP-MIB";

// Broadcom

$os = "bcm963";
$config['os'][$os]['text']                  = "Broadcom BCM963xx";
$config['os'][$os]['icon']                  = "broadcom";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysDescr'][]            = "/bcm963/i";

// Procera

$os = "plos";
$config['os'][$os]['text']                  = "PacketLogic";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_ucd_memory";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['icon']                  = "procera";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.15397.2";

// Mellanox
$os = "mlnx-os";
$config['os'][$os]['text']                  = "MLNX-OS";
$config['os'][$os]['group']                 = "mellanox";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mellanox";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.33049";

// Motorola

$os = "netopia";
$config['os'][$os]['text']                  = "Motorola Netopia";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.304.2.2.";

// Tranzeo

$os = "tranzeo";
$config['os'][$os]['text']                  = "Tranzeo";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysDescr'][]            = "/^Tranzeo/";

// Exalt

$os = "exalt";
$config['os'][$os]['text']                  = "Exalt";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25651.1.2";
$config['os'][$os]['mibs'][]                = "ExaltComProducts";

// Alvarion

$os = "breeze";
$config['os'][$os]['text']                  = "Alvarion";
$config['os'][$os]['icon']                  = "alvarion";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12394.4.1.";
$config['os'][$os]['mibs'][]                = "ALVARION-DOT11-WLAN-MIB";

$os = "breezemax";
$config['os'][$os]['text']                  = "Alvarion";
$config['os'][$os]['icon']                  = "alvarion";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.12394.1.";
//$config['os'][$os]['mibs'][]                = "ALVARION-DOT11-WLAN-MIB";

// D-Link

$os = "dlinkap";
$config['os'][$os]['text']                  = "D-Link Access Point";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "dlink";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.171.10.37";

$os = "dlinkvoip";
$config['os'][$os]['text']                  = "D-Link VoIP Gateway";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['icon']                  = "dlink";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.171.10.33";

$os = "dlinkdpr";
$config['os'][$os]['text']                  = "D-Link Print Server";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "dlink";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.171.11.10.1";

$os = "dlink";
$config['os'][$os]['text']                  = "D-Link Switch";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "dlink";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.171.10.";
$config['os'][$os]['mibs'][]                = "AGENT-GENERAL-MIB";

// TP-LINK

$os = "tplinkap";
$config['os'][$os]['text']                  = "TP-LINK Access Point";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "tplink";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11863.1.1.2";
$config['os'][$os]['sysDescr'][]            = "/^Linux TL-W\w+ [\d\.\-]+LSDK/";

$os = "tplink";
$config['os'][$os]['text']                  = "TP-LINK Switch";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "tplink";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11863";
$config['os'][$os]['sysDescr'][]            = "/^Linux TL-S\w+ /";

// AXIS

$os = "axiscam";
$config['os'][$os]['text']                  = "AXIS Network Camera";
$config['os'][$os]['type']                  = "video";
$config['os'][$os]['icon']                  = "axis";
$config['os'][$os]['group']                 = "axis";
$config['os'][$os]['sysDescr'][]            = "/AXIS .*? (Network Camera|Video Server)/";

$os = "axisencoder";
$config['os'][$os]['text']                  = "AXIS Network Video Encoder";
$config['os'][$os]['type']                  = "video";
$config['os'][$os]['icon']                  = "axis";
$config['os'][$os]['group']                 = "axis";
$config['os'][$os]['sysDescr'][]            = "/AXIS .*? Video Encoder/";

$os = "axisdocserver";
$config['os'][$os]['text']                  = "AXIS Network Document Server";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['icon']                  = "axis";
$config['os'][$os]['group']                 = "axis";
$config['os'][$os]['sysDescr'][]            = "/^AXIS .*? Network Document Server/";

$os = "axisprintserver";
$config['os'][$os]['text']                  = "AXIS Network Print Server";
$config['os'][$os]['type']                  = "printer";
$config['os'][$os]['icon']                  = "axis";
$config['os'][$os]['group']                 = "axis";
$config['os'][$os]['sysDescr'][]            = "/^AXIS .*? Network Print Server/";

// Gamatronic

$os = "gamatronicups";
$config['os'][$os]['text']                  = "Gamatronic UPS Stack";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['mibs'][]                = "GAMATRONIC-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6050.5";
$config['os'][$os]['mibs'][]                = "GAMATRONIC-MIB";
$config['os'][$os]['over'][0]['graph']      = "device_voltage";
$config['os'][$os]['over'][0]['text']       = "Voltage";
$config['os'][$os]['over'][1]['graph']      = "device_current";
$config['os'][$os]['over'][1]['text']       = "Current";
$config['os'][$os]['over'][2]['graph']      = "device_power";
$config['os'][$os]['over'][2]['text']       = "Power";

// Powerware

$os = "powerware";
$config['os'][$os]['text']                  = "Powerware UPS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "eaton";
$config['os'][$os]['over'][0]['graph']      = "device_voltage";
$config['os'][$os]['over'][1]['graph']      = "device_current";
$config['os'][$os]['over'][2]['graph']      = "device_frequency";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.534";
$config['os'][$os]['mibs'][]                = "XUPS-MIB";

// Delta

$os = "deltaups";
$config['os'][$os]['text']                  = "Delta UPS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "delta";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2254.2.4";
$config['os'][$os]['mibs'][]                = "DeltaUPS-MIB";

// Liebert / Emerson

$os = "liebert";
$config['os'][$os]['vendor']                = "Emerson";
$config['os'][$os]['text']                  = "Liebert";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "emerson";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.476.1.42";
$config['os'][$os]['mibs'][]                = "UPS-MIB";
$config['os'][$os]['mibs'][]                = "LIEBERT-GP-ENVIRONMENTAL-MIB";

$os = "avocent";
$config['os'][$os]['vendor']                = "Emerson";
$config['os'][$os]['text']                  = "Avocent";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avocent";
$config['os'][$os]['sysDescr'][]            = "/^Avocent/";
$config['os'][$os]['sysDescr'][]            = "/^AlterPath/";

$os = "cyclades";
$config['os'][$os]['vendor']                = "Emerson";
$config['os'][$os]['text']                  = "Cyclades";
$config['os'][$os]['type']                  = "management";
$config['os'][$os]['sysDescr'][]            = "/^Cyclades/";
$config['os'][$os]['mib_dirs'][]            = "cyclades";
$config['os'][$os]['mibs'][]                = "ACS-MIB";

// Rittal

$os = "rittalcmc3";
$config['os'][$os]['vendor']                = "Rittal";
$config['os'][$os]['text']                  = "Rittal CMC-III-PU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2606.7";
$config['os'][$os]['mib_dirs'][]            = "rittal";
$config['os'][$os]['mibs'][]                = "RITTAL-CMC-III-MIB";

// Engenius

$os = "engenius";
$config['os'][$os]['text']                  = "EnGenius Access Point";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "engenius";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['over'][1]['text']       = "Wireless clients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14125.100.1.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14125.101.1.3";
$config['os'][$os]['mibs'][]                = "SENAO-ENTERPRISE-INDOOR-AP-CB-MIB";
$config['os'][$os]['mibs'][]                = "ENGENIUS-PRIVATE-MIB";
$config['os'][$os]['mibs'][]                = "ENGENIUS-MESH-MIB";

// Apple

$os = "airport";
$config['os'][$os]['text']                  = "Apple AirPort";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "apple";
$config['os'][$os]['sysDescr'][]            = "/^Apple AirPort/";
$config['os'][$os]['sysDescr'][]            = "/^(Apple )?Base Station V[\d\.]+ Compatible/";
$config['os'][$os]['mibs'][]                = "AIRPORT-BASESTATION-3-MIB";

// Microsoft

$os = "windows";
$config['os'][$os]['text']                  = "Microsoft Windows";
$config['os'][$os]['icons'][]               = "windows";
$config['os'][$os]['icons'][]               = "windows_old";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['processor_stacked']     = 1;
$config['os'][$os]['over'][0]['graph']      = "device_processor";
$config['os'][$os]['over'][1]['graph']      = "device_mempool";
$config['os'][$os]['over'][2]['graph']      = "device_storage";
$config['os'][$os]['over'][3]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.311.1.1.3";
$config['os'][$os]['sysDescr'][]            = "/Windows/";
$config['os'][$os]['mibs'][]                = "LSI-MegaRAID-SAS-MIB";
$config['os'][$os]['mibs'][]                = "MIB-Dell-10892";
#$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

// IBM

$os = "ibmnos";
$config['os'][$os]['text']                  = "IBM Networking Operating System";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ibm";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.26543.1.7.4";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.26543.1.7.6";
$config['os'][$os]['sysDescr'][]            = "/^IBM Networking Operating System/";
$config['os'][$os]['sysDescr'][]            = "/Blade Network Technologies/"; // Old bnt
$config['os'][$os]['sysDescr'][]            = "/^BNT /";

// NetAPP

$os = "netapp";
$config['os'][$os]['text']                  = "NetApp";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "netapp";
$config['os'][$os]['snmp']['max-rep']       = 50;
$config['os'][$os]['over'][0]['graph']      = "device_netapp_net_io";
$config['os'][$os]['over'][1]['graph']      = "device_netapp_ops";
$config['os'][$os]['over'][2]['graph']      = "device_netapp_disk_io";
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

$os = "procurve";
$config['os'][$os]['text']                  = "HP ProCurve";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.7.11.";  // ProCurve Switch
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.7.8.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.14.11.7";  // ProCurve Secure Router
$config['os'][$os]['sysDescr'][]            = "/^(HP )?ProCurve (?!AP|Access Point)/"; // Fallback for unknown sysObjectID (APs excludes)
$config['os'][$os]['mibs'][]                = "STATISTICS-MIB";
$config['os'][$os]['mibs'][]                = "NETSWITCH-MIB";
$config['os'][$os]['mibs'][]                = "HP-ICF-CHASSIS";

$os = "procurve-ap";
$config['os'][$os]['text']                  = "HP ProCurve Access Point";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.14.11.6";  // ProCurve Access Point
#$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11898.2.4.6";   // ProCurve AP. WARNING, this is multibranded sysObjectID!
$config['os'][$os]['sysDescr'][]            = "/^(HP )?ProCurve (AP|Access Point)/";

$os = "hpvc";
$config['os'][$os]['text']                  = "HP Virtual Connect";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.7.11.33"; // Ethernet Blade Switch for HP c-Class BladeSystem
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.5.7.5.1";     // VC Flex-10

$os = "hpux";
$config['os'][$os]['text']                  = "HP-UX";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.3.2";

$os = "hpstorage";
$config['os'][$os]['text']                  = "HP StorageWorks";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.2.51";

$os = "hpilo";
$config['os'][$os]['text']                  = "HP iLO Management";
$config['os'][$os]['type']                  = "management";
$config['os'][$os]['icon']                  = "hp";
//$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.5.7.1.2";  // Onboard Administrator
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.5.7.3.2";  // iLO Management Processor
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.232.9.4.10";  // iLO 4
$config['os'][$os]['sysDescr'][]            = "/^Integrated Lights\-Out \d/";
$config['os'][$os]['mibs'][]                = "CPQHLTH-MIB";
$config['os'][$os]['mibs'][]                = "CPQIDA-MIB";

$os = "3com";
$config['os'][$os]['text']                  = "3Com OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "3com";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.43";

$os = "h3c";
$config['os'][$os]['text']                  = "H3C Comware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "h3c";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2011.10"; // Not correct, this is Huawei VRP
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25506.1.";

$os = "hh3c";
$config['os'][$os]['text']                  = "HP Comware";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25506";
$config['os'][$os]['mibs'][]                = "HH3C-ENTITY-EXT-MIB";
$config['os'][$os]['mibs'][]                = "HH3C-TRANSCEIVER-INFO-MIB";

$os = "speedtouch";
$config['os'][$os]['text']                  = "Thomson Speedtouch";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['sysDescr'][]            = "/TG585v7/";
$config['os'][$os]['sysDescr'][]            = "/SpeedTouch /";
$config['os'][$os]['sysDescr'][]            = "/^ST\d/";

// ZyXEL

$os = "zywall";
$config['os'][$os]['text']                  = "ZyXEL ZyWALL";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
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
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_fdb_count";
$config['os'][$os]['over'][2]['graph']      = "device_processor";
$config['os'][$os]['over'][3]['graph']      = "device_mempool";
$config['os'][$os]['discovery_os']          = "allied";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207";
$config['os'][$os]['mib_dirs'][]            = "allied";
$config['os'][$os]['mibs'][]                = "AT-SYSINFO-MIB";

$os = "alliedwareplus";
$config['os'][$os]['text']                  = "AlliedWare Plus";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "alliedtelesis";
$config['os'][$os]['discovery_os']          = "allied";
$config['os'][$os]['mib_dirs'][]            = "alliedwareplus";
$config['os'][$os]['mibs'][]                = "AT-SYSINFO-MIB";

// This is only to be used for RADLAN-based PowerConnects

$os = "allied-radlan";
$config['os'][$os]['text']                  = "Allied Telesis (RADLAN)";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "alliedtelesis";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207.1.4.125"; // ATI 8000S
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.207.1.4.126"; // ATI AT-8000S
//$config['os'][$os]['sysDescr'][]            = "/ATI (AT\-)?8000/"; // Already detected by sysObjectID
$config['os'][$os]['mibs'][]                = "RADLAN-HWENVIROMENT";
$config['os'][$os]['mibs'][]                = "RADLAN-rndMng";

$os = "actelis";
$config['os'][$os]['text']                  = "Actelis";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5468.1";

$os = "microsens";
$config['os'][$os]['text']                  = "Microsens";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_bits";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3181.10.3";
$config['os'][$os]['mibs'][]                = "MS-SWITCH30-MIB";

$os = "mgeups";
$config['os'][$os]['text']                  = "MGE UPS";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "mge";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.705.1";
$config['os'][$os]['sysDescr'][]            = "/^MGE UPS/";
$config['os'][$os]['mibs'][]                = "MG-SNMP-UPS-MIB";

$os = "mgepdu";
$config['os'][$os]['text']                  = "MGE PDU";
$config['os'][$os]['group']                 = "pdu";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "mge";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.705.2";

// APC

$os = "apc";
$config['os'][$os]['text']                  = "APC OS";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['over'][2]['graph']      = "device_power";
$config['os'][$os]['over'][3]['graph']      = "device_temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.318";
$config['os'][$os]['mibs'][]                = "PowerNet-MIB";
$config['os'][$os]['mib_blacklist'][]       = "BGP4-MIB";
$config['os'][$os]['mib_blacklist'][]       = "HOST-RESOURCES-MIB";

$os = "oec";
$config['os'][$os]['text']                  = "OEC PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_uptime";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.29640.1.2.4";
$config['os'][$os]['mibs'][]                = "APNL-MODULAR-PDU-MIB";

$os = "netbotz";
$config['os'][$os]['text']                  = "APC Netbotz";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "apc";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5528";
$config['os'][$os]['mibs'][]                = "NETBOTZV2-MIB";

$os = "pcoweb";
$config['os'][$os]['text']                  = "Carel pCOWeb";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['icon']                  = "carel";
$config['os'][$os]['icons'][]               = "uniflair";
$config['os'][$os]['mibs'][]                = "CAREL-ug40cdz-MIB";

// Socomec

$os = "netvision";
$config['os'][$os]['text']                  = "Socomec Net Vision";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['sysDescr'][]            = "/^Net Vision/";
$config['os'][$os]['mib_dirs'][]            = "socomec";
$config['os'][$os]['mibs'][]                = "SOCOMECUPS-MIB";

/* other Socomec products, newer tested and not supported:
$os = "pduvision";
$config['os'][$os]['text']                  = "Socomec PDU Vision";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['sysDescr'][]            = "/^PDU Vision/";
$config['os'][$os]['mib_dirs'][]            = "socomec";
$config['os'][$os]['mibs'][]                = "SOCOMECPDU-MIB";

$os = "ipdu";
$config['os'][$os]['text']                  = "Socomec iPDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['sysDescr'][]            = "/^iPDU/";
$config['os'][$os]['mib_dirs'][]            = "socomec";
$config['os'][$os]['mibs'][]                = "SOCOMECUPS-MIB-v2"; // This old MIB version, not compatible with SOCOMECUPS-MIB
*/

$os = "areca";
$config['os'][$os]['text']                  = "Areca RAID Subsystem";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.18928.1";
$config['os'][$os]['mibs'][]                = "ARECA-SNMP-MIB";

$os = "netmanplus";
$config['os'][$os]['text']                  = "NetMan Plus";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['mibs'][]                = "UPS-MIB";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5491.6";

$os = "cs121";
$config['os'][$os]['text']                  = "Generex UPS";
$config['os'][$os]['group']                 = "ups";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_voltage";
$config['os'][$os]['icon']                  = "generex";
$config['os'][$os]['sysDescr'][]            = "/^CS121 v/";
$config['os'][$os]['mibs'][]                = "UPS-MIB";

$os = "sensorgateway";
$config['os'][$os]['text']                  = "ServerRoom Sensor Gateway";
$config['os'][$os]['group']                 = "environment";
$config['os'][$os]['icon']                  = "serverscheck";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['sysDescr'][]            = "/^Temperature & Sensor Gateway/";
$config['os'][$os]['mibs'][]                = "SENSORGATEWAY-MIB";

$os = "sensorprobe";
$config['os'][$os]['text']                  = "AKCP SensorProbe";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "akcp";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['sysDescr'][]            = "/SensorProbe/i";
$config['os'][$os]['mibs'][]                = "SPAGENT-MIB";

$os = "roomalert";
$config['os'][$os]['text']                  = "AVTECH RoomAlert";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "avtech";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['sysDescr'][]            = "/^Room ?Alert/";
$config['os'][$os]['mibs'][]                = "ROOMALERT24E-MIB";
$config['os'][$os]['mibs'][]                = "ROOMALERT4E-MIB";

$os = "minkelsrms";
$config['os'][$os]['text']                  = "Minkels RMS";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['discovery_os']          = "sensorprobe";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['sysDescr'][]            = "/8VD-X20/";
$config['os'][$os]['mibs'][]                = "SPAGENT-MIB";

$os = "ipoman";
$config['os'][$os]['text']                  = "Ingrasys iPoMan";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['icon']                  = "ingrasys";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['over'][1]['graph']      = "device_power";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2468.1.4.2.1";
$config['os'][$os]['mibs'][]                = "IPOMANII-MIB";

$os = "wxgoos";
$config['os'][$os]['text']                  = "ITWatchDogs Goose";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['over'][1]['graph']      = "device_humidity";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.17373";
$config['os'][$os]['mibs'][]                = "IT-WATCHDOGS-MIB-V3";
$config['os'][$os]['mibs'][]                = "IT-WATCHDOGS-V4-MIB";

$os = "papouch";
$config['os'][$os]['text']                  = "Papouch Probe";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['sysDescr'][]            = "/^(SNMP )?TME$/";
$config['os'][$os]['sysDescr'][]            = "/^TH2E$/";
$config['os'][$os]['mibs'][]                = "Papouch-SMI";

$os = "cometsystem-p85xx";
$config['os'][$os]['text']                  = "Comet System P85xx";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['icon']                  = "comet";
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['mibs'][]                = "P8510-MIB";

// Printers
//FIXME. Currently not detected printers (sysObjectID, sysDescr):
//.1.3.6.1.4.1.641.4.0 4900   Series version NET.CH.N208 kernel 2.6.12.5-88w8xx8 All-N-1
//.1.3.6.1.4.1.641.1.71106853 Laser Printer 66 version NR.APS.N310 kernel 2.6.18.5 All-N-1
//.1.3.6.1.4.1.641.2.71106878 Color Laser Printer 59-MFP version NR.APS.N434 kernel 2.6.18.5 All-N-1
//.1.3.6.1.4.1.641.1          FLP T630 version 55.10.19 kernel 2.4.0-test6 All-N-1
//.1.3.6.1.4.1.367.1.1        RFG SP 3300 Series OS 1.50.02.44 06-17-2009;Engine 1.01.25;NIC V4.01.03 06-02-2009;S/N S4099302659W
//.1.3.6.1.4.1.367.1.1        Gestetner MP 161L/DSm416s / Gestetner Network Printer D model
//.1.3.6.1.4.1.367.1.1        SAVIN C3030 1.62.1 / SAVIN Network Printer C model / SAVIN Network Scanner C model
//.1.3.6.1.4.1.367.1.1        LANIER MP 7001/LD370 1.20 / LANIER Network Printer C model / LANIER Network Scanner C model
//.1.3.6.1.4.1.367.1.1        infotec ISC 2020 1.68 / infotec Network Printer C model / infotec Network Scanner C model / infotec Network Facsimile C model
//.1.3.6.1.4.1.18334.1.2.1.2.1.69.4.1 Fiery PRO80 70-60C-KM
//.1.3.6.1.4.1.18334.1.2.1.2.1.58.1.2 Color MF30-1

$os = "dell-laser";
$config['os'][$os]['text']                  = "Dell Laser";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10898.2.100.10";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10898.10.51";
$config['os'][$os]['sysDescr'][]            = "/^Dell (?:\w+ )?Laser Printer/";
$config['os'][$os]['sysDescr'][]            = "/^Dell .*?MFP/";

$os = "ricoh";
$config['os'][$os]['text']                  = "Ricoh Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "ricoh";
$config['os'][$os]['sysDescr'][]            = "/RICOH Network Printer/";
$config['os'][$os]['sysDescr'][]            = "/^RICOH$/";
//This sysObjectID intersected with many other printers, use sysDescr instead
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.367.1.1";

$os = "lexmark";
$config['os'][$os]['text']                  = "Lexmark Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "lexmark";
$config['os'][$os]['sysDescr'][]            = "/^Lexmark /";
//This sysObjectID intersected with many other printers, use sysDescr instead
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.641.1";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.641.2";

$os = "lg";
$config['os'][$os]['text']                  = "LG Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.38191.6.2.2";
$config['os'][$os]['sysDescr'][]            = "/^LG L/";

$os = "ibm-infoprint";
$config['os'][$os]['text']                  = "IBM Infoprint";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "ibm";
$config['os'][$os]['sysDescr'][]            = "/^(IBM )?Info[Pp]rint \d+/";

$os = "sindoh";
$config['os'][$os]['text']                  = "SINDOH Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysDescr'][]            = "/^SINDO(RICO)?H /";

$os = "nrg";
$config['os'][$os]['text']                  = "NRG Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "nrg";
$config['os'][$os]['sysDescr'][]            = "/NRG Network Printer/";

$os = "epson";
$config['os'][$os]['text']                  = "Epson Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "epson";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1248.1.1.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1248.1.2.1";

$os = "xerox";
$config['os'][$os]['text']                  = "Xerox Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.253.8.62.1.";

$os = "fuji-xerox";
$config['os'][$os]['text']                  = "Fuji Xerox Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "xerox";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.297.1.11.93.1.";

$os = "samsung";
$config['os'][$os]['text']                  = "Samsung Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysDescr'][]            = "/Samsung (ML|CL|SC)/";
$config['os'][$os]['sysDescr'][]            = "/^SAMSUNG NETWORK PRINTER/";

$os = "canon";
$config['os'][$os]['text']                  = "Canon Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1602.4.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1602.4.7";

$os = "jetdirect";
$config['os'][$os]['text']                  = "HP Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['icon']                  = "hp";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.11.1"; // Sometime intersected with Samsung printers
$config['os'][$os]['sysDescr'][]            = "/^(HP ETHERNET|Type) .*?,JETDIRECT(,|$)/";
$config['os'][$os]['sysDescr'][]            = "/^(HP ETHERNET|Type) .*?,LaserJet(,|$)/";
$config['os'][$os]['sysDescr'][]            = "/^(HP ETHERNET MULTI-ENVIRONMENT)/";

$os = "olivetti";
$config['os'][$os]['text']                  = "Olivetti Printer";
$config['os'][$os]['group']                 = "printer";
//.1.3.6.1.4.1.18334.1.2.1.2.1.106.2.4  Generic 28C-6e
//.1.3.6.1.4.1.18334.1.2.1.2.1.64.2.1   Generic 36BW-4
//.1.3.6.1.4.1.18334.1.2.1.2.1.48.2.1   Generic 45C-5
$config['os'][$os]['sysDescr'][]            = "/^Generic \w+-\w+$/";

$os = "sharp";
$config['os'][$os]['text']                  = "Sharp Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "sharp";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2385.3.1.";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.3369.1.1.2.40";

$os = "okilan";
$config['os'][$os]['text']                  = "OKI Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "oki";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2001.1";

$os = "brother";
$config['os'][$os]['text']                  = "Brother Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2435.2.3.9.1";
$config['os'][$os]['sysDescr'][]            = "/^Brother NC-/"; // Sometime sysObjectID empty

$os = "konica";
$config['os'][$os]['text']                  = "Konica-Minolta Printer/Copier";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.18334.1.1.1.2.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2590.1.1.1.2.1";

$os = "develop";
$config['os'][$os]['text']                  = "Develop Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "konica";
//.1.3.6.1.4.1.18334.1.2.1.2.1.57.2.1   Develop ineo+ 220
//.1.3.6.1.4.1.18334.1.2.1.2.1.64.3.1   Develop ineo 363
$config['os'][$os]['sysDescr'][]            = "/^Develop ineo/";

$os = "kyocera";
$config['os'][$os]['text']                  = "Kyocera Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['ifname']                = 1;
//.1.3.6.1.4.1.1347.43.5.1.1.1  KYOCERA Print System IB-110 Ver 1.2.0
//.1.3.6.1.4.1.1347.41          KYOCERA MITA Printing System
//.1.3.6.1.4.1.1347.41          KYOCERA Document Solutions Printing System
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1347.41"; // Intersected with other printers
$config['os'][$os]['sysDescr'][]            = "/^KYOCERA .*?Print/";

$os = "estudio";
$config['os'][$os]['text']                  = "Toshiba Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "toshiba";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1129.2.3.";

$os = "panasonic";
$config['os'][$os]['text']                  = "Panasonic Printer";
$config['os'][$os]['group']                 = "printer";
$config['os'][$os]['icon']                  = "panasonic";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.258.406.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.258.406.3";

$os = "sentry3";
$config['os'][$os]['text']                  = "ServerTech Sentry3";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['icon']                  = "servertech";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1718.3";
$config['os'][$os]['mibs'][]                = "Sentry3-MIB";

$os = "gude-epc";
$config['os'][$os]['text']                  = "Gude Expert Power Control";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['icon']                  = "gude";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.1";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.28507.6";
$config['os'][$os]['mibs'][]                = "GUDEADS-EPC8X-MIB";
$config['os'][$os]['mibs'][]                = "GUDEADS-EPC2X6-MIB";

$os = "gude-pdu";
$config['os'][$os]['text']                  = "Gude Expert PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
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
$config['os'][$os]['icon']                  = "geist";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21239.2";
$config['os'][$os]['mibs'][]                = "GEIST-MIB-V3";

$os = "geist-climate";
$config['os'][$os]['text']                  = "Geist Climate Monitor";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['icon']                  = "geist";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21239.5";
$config['os'][$os]['mibs'][]                = "GEIST-V4-MIB";

$os = "raritan";
$config['os'][$os]['text']                  = "Raritan PDU";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['icon']                  = "raritan";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.13742";
//$config['os'][$os]['sysDescr'][]            = "/^Raritan/";
$config['os'][$os]['mibs'][]                = "PDU-MIB";

$os = "mrvld";
$config['os'][$os]['text']                  = "MRV LambdaDriver";
$config['os'][$os]['group']                 = "mrv";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mrv";
$config['os'][$os]['sysDescr'][]            = "/^LambdaDriver/";
$config['os'][$os]['mibs'][]                = "OA-SFP-MIB";
$config['os'][$os]['mibs'][]                = "OADWDM-MIB";

$os = "mrvnbs";
$config['os'][$os]['text']                  = "MRV";
$config['os'][$os]['group']                 = "mrv";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "mrv";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.629";
$config['os'][$os]['mibs'][]                = "NBS-CMMC-MIB";

$os = "poweralert";
$config['os'][$os]['text']                  = "Tripp Lite PowerAlert";
$config['os'][$os]['type']                  = "power";
$config['os'][$os]['over'][0]['graph']      = "device_current";
$config['os'][$os]['icon']                  = "tripplite";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.850";
$config['os'][$os]['mibs'][]                = "UPS-MIB";

$os = "jdsu_edfa";
$config['os'][$os]['text']                  = "JDSU OEM Erbium Dotted Fibre Amplifier";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "avocent";
$config['os'][$os]['mibs'][]                = "NSCRTV-ROOT";

$os = "symbol";
$config['os'][$os]['text']                  = "Symbol AP";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "symbol";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.388";

$os = "firebox";
$config['os'][$os]['text']                  = "Watchguard Firebox";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['discovery_os']          = "watchguard";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['icon']                  = "watchguard";
$config['os'][$os]['sysDescr'][]            = "/^WatchGuard Fireware/";
$config['os'][$os]['sysDescr'][]            = "/^XTM/";
$config['os'][$os]['mibs'][]                = "WATCHGUARD-SYSTEM-STATISTICS-MIB";

$os = "panos";
$config['os'][$os]['text']                  = "PanOS";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "panos";
//$config['os'][$os]['snmp']['max-rep']       = 50; // PanOS seems to fail here.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25461.2";
$config['os'][$os]['mib_dirs'][]            = "paloalto";
$config['os'][$os]['mibs'][]                = "PAN-COMMON-MIB";

$os = "arubaos";
$config['os'][$os]['text']                  = "ArubaOS";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "arubaos";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_arubacontroller_numaps";
$config['os'][$os]['over'][1]['graph']      = "device_arubacontroller_numclients";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6486.800.1.1.2.2"; // Seems as wrong, because intersects with OmniStack and AOS-W
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14823";
$config['os'][$os]['sysDescr'][]            = "/^ArubaOS/";
$config['os'][$os]['mibs'][]                = "WLSX-SWITCH-MIB";

$os = "trapeze";
$config['os'][$os]['text']                  = "Juniper Wireless";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "juniper";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14525.3.3";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.14525.3.1";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_wifi_clients";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-CLIENT-SESSION-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-AP-STATUS-MIB";
$config['os'][$os]['mibs'][]                = "TRAPEZE-NETWORKS-AP-CONFIG-MIB";

## Lancom devices - lcos is new, unified-MIB software. The others are legacy bullshit.

$os = "lcos";
$config['os'][$os]['text']                  = "LCOS";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['icon']                  = "lcos";
$config['os'][$os]['ifname']                = TRUE;
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2356.11";
$config['os'][$os]['mibs'][]                = "LCOS-MIB";

$os = "lancom-l54-dual";                    // Yes. Model-specific OS type for model-specific MIB.
$config['os'][$os]['text']                  = "LCOS (L-54 Dual)";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['ifname']                = TRUE;
$config['os'][$os]['icon']                  = "lcos";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2356.600.3.55";

$os = "lancom-l310";                        // Yes. Model-specific OS type for model-specific MIB.
$config['os'][$os]['text']                  = "LCOS (L-310)";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['ifname']                = TRUE;
$config['os'][$os]['icon']                  = "lcos";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2356.600.6.310";
$config['os'][$os]['mibs'][]                = "lancom-l310-mib";

$os = "lancom-c54g";                        // Yes. Model-specific OS type for model-specific MIB.
$config['os'][$os]['text']                  = "LCOS (C-54g)";
$config['os'][$os]['type']                  = "wireless";
$config['os'][$os]['ifname']                = TRUE;
$config['os'][$os]['icon']                  = "lcos";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2356.600.4.54";


$os = "dsm";
$config['os'][$os]['text']                  = "Synology DSM";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['type']                  = "storage";
$config['os'][$os]['icon']                  = "synology";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_ucd_memory";
$config['os'][$os]['mibs'][]                = "SYNOLOGY-SYSTEM-MIB";
$config['os'][$os]['mibs'][]                = "SYNOLOGY-DISK-MIB";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-SENSOR-MIB";
$config['os'][$os]['mib_blacklist'][]       = "LSI-MegaRAID-SAS-MIB";
$config['os'][$os]['mib_blacklist'][]       = "BGP4-MIB";

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
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['mibs'][]                = "FROGFOOT-RESOURCES-MIB";
$config['os'][$os]['mib_blacklist'][]       = "BGP4-MIB";

$os = "airos";
$config['os'][$os]['text']                  = "Ubiquiti AirOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "linux";
$config['os'][$os]['icon']                  = "ubiquiti";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['mibs'][]                = "FROGFOOT-RESOURCES-MIB";

$os = "edgeos";
$config['os'][$os]['text']                  = "Ubiquiti EdgeOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['icon']                  = "ubiquiti";
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.30803"; // EdgeOS < 1.5, but overlaps with Vyatta
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.41112"; // EdgeOS >    = 1.5
$config['os'][$os]['sysDescr'][]            = "/^Edge(OS|Switch)/";
$config['os'][$os]['mibs'][]                = "UBNT-MIB";

// Draytek firewall/routers

$os = "draytek";
$config['os'][$os]['text']                  = "Draytek";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "draytek";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.7367";
$config['os'][$os]['sysDescr'][]            = "/DrayTek/i";

// SmartEdge OS

$os = "seos";
$config['os'][$os]['text']                  = "SmartEdge OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ericsson";
$config['os'][$os]['sysDescr'][]            = "/SmartEdge.*? SEOS/";
$config['os'][$os]['mibs'][]                = "RBN-ENVMON-MIB";
$config['os'][$os]['mibs'][]                = "RBN-CPU-METER-MIB";
$config['os'][$os]['mibs'][]                = "RBN-MEMORY-MIB";

// Barracuda NG firewall

$os = "barracudangfw";
$config['os'][$os]['text']                  = "Barracuda NG firewall";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "barracuda";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.10704";

// Audiocodes

$os = "audiocodes";
$config['os'][$os]['text']                  = "Audiocodes";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['icon']                  = "audiocodes";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5003";
$config['os'][$os]['mibs'][]                = "AC-SYSTEM-MIB";

// ShoreTel

$os = "shoretelos";
$config['os'][$os]['text']                  = "ShoreTel OS";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.5329";

// Mitel

$os = "mcd";
$config['os'][$os]['text']                  = "Mitel Controller";
$config['os'][$os]['type']                  = "voip";
$config['os'][$os]['icon']                  = "mitel";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.1027.1.2.3";
#$config['os'][$os]['mibs'][]                = "MITEL-MIB";
#$config['os'][$os]['mibs'][]                = "MITEL-IperaVoiceLAN-MIB";

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
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21796.3.3";
$config['os'][$os]['mib_dirs'][]            = "hwgroup";
$config['os'][$os]['mibs'][]                = "POSEIDON-MIB";

$os = "hwg-ste";
$config['os'][$os]['text']                  = "HWg-STE";
$config['os'][$os]['type']                  = "environment";
$config['os'][$os]['nobulk']                = 1;
$config['os'][$os]['over'][0]['graph']      = "device_temperature";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21796.4.1";
$config['os'][$os]['mib_dirs'][]            = "hwgroup";
$config['os'][$os]['mibs'][]                = "STE-MIB";

$os = "iqnos";
$config['os'][$os]['text']                  = "Infinera IQ";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "infinera";
$config['os'][$os]['mib_dirs'][]            = "infinera";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.21296";
// MIBs disabled until not implemented
//$config['os'][$os]['mibs'][]                = "INFINERA-REG-MIB";
//$config['os'][$os]['mibs'][]                = "INFINERA-TC-MIB";

$os = "picos";
$config['os'][$os]['text']                  = "Pica8 OS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "pica8";
$config['os'][$os]['sysDescr'][]            = "/^Pica8/";
$config['os'][$os]['mib_dirs'][]            = "pica8";
// MIBs disabled until not implemented
//$config['os'][$os]['mibs'][]                = "PICA-PRIVATE-MIB";

// Radware

$os = "radware";
$config['os'][$os]['text']                  = "Radware DefensePro";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['icon']                  = "radware";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.89.1.1.62.16";
$config['os'][$os]['sysDescr'][]            = "/^DefensePro/";
$config['os'][$os]['sysDescr'][]            = "/Check Point DDoS Protector/";
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
$config['os'][$os]['over'][1]['graph']      = "device_processor";

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
$config['os'][$os]['group']                 = "unix";
$config['os'][$os]['type']                  = "management";
$config['os'][$os]['icon']                  = "opengear";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.";   // Wildcard sysObjectID
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.16.";  // Wildcard sysObjectID
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.1";  //CM4001
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.2";  //CM4002
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.3";  //CM4008
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.10"; //CM41xx
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.20"; //SD4001
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.21"; //SD4002
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.22"; //SD4008
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.23"; //SD4001DW
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.24"; //SD4002DX
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.31"; //CMx86
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.40"; //CMS61xx
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.41"; //Lighthouse
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.50"; //IM4004
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.60"; //IM42xx
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.61"; //IM72xx
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.70"; //KCS61xx
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.80"; //ACM500x
//$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.25049.1.81"; //ACM550x
// MIBs disabled until not implemented
//$config['os'][$os]['mibs'][]              = "OG-CONNECT-MIB";
//$config['os'][$os]['mibs'][]              = "OG-DATA-MIB";
//$config['os'][$os]['mibs'][]              = "OG-FAILOVER-MIB";
//$config['os'][$os]['mibs'][]              = "OG-HOST-MIB";
//$config['os'][$os]['mibs'][]              = "OG-PATTERN-MIB";
//$config['os'][$os]['mibs'][]              = "OG-PRODUCTS-MIB";
//$config['os'][$os]['mibs'][]              = "OG-SENSOR-MIB";
//$config['os'][$os]['mibs'][]              = "OG-SIGNAL-MIB";
//$config['os'][$os]['mibs'][]              = "OG-SMI-MIB";
$config['os'][$os]['mibs'][]              = "OG-STATUS-MIB";
//$config['os'][$os]['mibs'][]              = "OG-STATUSv2-MIB";
//$config['os'][$os]['mibs'][]              = "OG-UPS-MIB";
//$config['os'][$os]['mibs'][]              = "OGTRAP-MIB";
//$config['os'][$os]['mibs'][]              = "OGTRAPv2-MIB";

$os = "zeustm";
$config['os'][$os]['text']                  = "Riverbed Stingray";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "riverbed";
$config['os'][$os]['discovery_os']          = "linux";
// MIBs disabled until not implemented
//$config['os'][$os]['mibs'][]                = "ZXTM-MIB";
//$config['os'][$os]['mibs'][]                = "ZXTM-MIB-SMIv2";

// SmartOptics M-series hardware with SmartOS software

$os = "smartos";
$config['os'][$os]['vendor']            = "SmartOptics";
$config['os'][$os]['text']              = "SmartOS";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['icon']              = "smartoptics";
$config['os'][$os]['sysObjectID'][]     = ".1.3.6.1.4.1.30826.1";
$config['os'][$os]['mibs'][]            = "MSERIES-ENVMON-MIB";
$config['os'][$os]['mibs'][]            = "MSERIES-ALARMS-MIB";
$config['os'][$os]['mibs'][]            = "MSERIES-PORT-MIB";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "NMB Traffic";

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
