<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage tests
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/////////////////////////////////////////////////////////
//  NO CHANGES TO THIS FILE, IT IS NOT USER-EDITABLE   //
/////////////////////////////////////////////////////////
//                 FAKE DEFINITIONS                    //
/////////////////////////////////////////////////////////

$GLOBALS['cache']['db_version'] = 999; // Set fake DB version
setlocale(LC_ALL, 'C');
putenv('LC_ALL=C');
define('OBS_DEBUG', 0);

/*
set_include_path(dirname(__FILE__) . "/../../includes/pear" . PATH_SEPARATOR . get_include_path());

require("Net/IPv4.php");
require("Net/IPv6.php");
require("Console/Color2.php");
*/

unset($config['os']['default']); // Override default for tests
$config['os']['default']['graphs'][0]                 = "device_bits";
$config['os']['default']['graphs'][1]                 = "device_uptime";
$config['os']['default']['graphs'][2]                 = "device_ping";
// MIBs enabled for any os (except blacklisted mibs)
$config['os']['default']['mibs'][]                  = "ENTITY-MIB";
$config['os']['default']['mibs'][]                  = "ENTITY-SENSOR-MIB";
$config['os']['default']['mibs'][]                  = "HOST-RESOURCES-MIB";
$config['os']['default']['mibs'][]                  = "Q-BRIDGE-MIB";
$config['os']['default']['mibs'][]                  = "LLDP-MIB";
//$config['os']['default']['mibs'][]                  = "CISCO-CDP-MIB"; // FIXME. See in module discovery-protocols

$os_group = "test_unix";
$config['os_group'][$os_group]['type']              = "server";
$config['os_group'][$os_group]['processor_stacked'] = 1;
$config['os_group'][$os_group]['graphs'][0]           = "device_processor";
$config['os_group'][$os_group]['graphs'][1]           = "device_ucd_memory";
$config['os_group'][$os_group]['mibs'][]            = "UCD-SNMP-MIB";         // Should be before HOST-RESOURCES-MIB (in storage)
$config['os_group'][$os_group]['mibs'][]            = "HOST-RESOURCES-MIB";   // There duplicate entry as in default, for correct order!
$config['os_group'][$os_group]['mibs'][]            = "LSI-MegaRAID-SAS-MIB";

$os_group = "test_cisco";
$config['os_group'][$os_group]['type']              = "network";
$config['os_group'][$os_group]['graphs'][0]           = "device_bits";
$config['os_group'][$os_group]['graphs'][1]           = "device_processor";
$config['os_group'][$os_group]['graphs'][2]           = "device_mempool";
$config['os_group'][$os_group]['comments']          = "/^\s*!/";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IETF-IP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-SENSOR-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-VTP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENVMON-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-QFP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IP-STAT-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-FIREWALL-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENHANCED-MEMPOOL-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-MEMORY-POOL-MIB"; // Keep this below CISCO-ENHANCED-MEMPOOL-MIB, checks for duplicates.
$config['os_group'][$os_group]['mibs'][]            = "CISCO-PROCESS-MIB"; // Goes after "CISCO-MEMORY-POOL-MIB" and "CISCO-ENHANCED-MEMPOOL-MIB" cos Cisco suck.

$os_group = "test_black";
$config['os_group'][$os_group]['type']              = "network";
$config['os_group'][$os_group]['graphs'][0]           = "device_bits";
$config['os_group'][$os_group]['graphs'][1]           = "device_processor";
$config['os_group'][$os_group]['graphs'][2]           = "device_mempool";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IETF-IP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-SENSOR-MIB";
$config['os_group'][$os_group]['mib_blacklist'][]   = "Q-BRIDGE-MIB";

$os = "test_generic";
$config['os'][$os]['text']                  = "Generic Device";
$config['os'][$os]['group']                 = "test_unix"; // Try detect generic device as generic Unix

// Linux-based OSes here please.

$os = "test_linux";
$config['os'][$os]['text']                  = "Linux";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "test_unix";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['graphs'][0]               = "device_processor";
$config['os'][$os]['graphs'][1]               = "device_ucd_memory";
$config['os'][$os]['graphs'][2]               = "device_storage";
$config['os'][$os]['graphs'][3]               = "device_bits";
$config['os'][$os]['mibs'][]                = "LM-SENSORS-MIB";
$config['os'][$os]['mibs'][]                = "SUPERMICRO-HEALTH-MIB";
$config['os'][$os]['mibs'][]                = "MIB-Dell-10892";
$config['os'][$os]['mibs'][]                = "CPQHLTH-MIB";
$config['os'][$os]['mibs'][]                = "CPQIDA-MIB";
$config['os'][$os]['realtime']              = 15;

$os = "test_freebsd";
$config['os'][$os]['text']                  = "FreeBSD";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "test_black";
$config['os'][$os]['discovery_os']          = "freebsd";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.8072.3.2.8";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-SENSOR-MIB";

$os = "test_ios";
$config['os'][$os]['text']                  = "Cisco IOS";
$config['os'][$os]['group']                 = "test_cisco";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['discovery_os']          = "cisco";
$config['os'][$os]['snmp']['max-rep']       = 100;
$config['os'][$os]['graphs'][0]               = "device_bits";
$config['os'][$os]['graphs'][1]               = "device_processor";
$config['os'][$os]['graphs'][2]               = "device_mempool";
$config['os'][$os]['icon']                  = "cisco";

$os = "test_ciscosb";
$config['os'][$os]['text']                  = "Cisco Small Business";
$config['os'][$os]['ifname']                = 1;
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "ciscosb";
$config['os'][$os]['graphs'][0]               = "device_bits";
$config['os'][$os]['graphs'][1]               = "device_processor";
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

$os = "test_junos";
$config['os'][$os]['text']                  = "Juniper JunOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
// $config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['graphs'][0]               = "device_bits";
$config['os'][$os]['graphs'][1]               = "device_processor";
$config['os'][$os]['graphs'][2]               = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.2636";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-MIB";
$config['os'][$os]['mib_blacklist'][]       = "ENTITY-SENSOR-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-ALARM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-DOM-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-SRX5000-SPU-MONITORING-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-VLAN-MIB";
$config['os'][$os]['mibs'][]                = "JUNIPER-MAC-MIB";

$os = "test_drac";
$config['os'][$os]['text']                  = "Dell iDRAC";
$config['os'][$os]['icon']                  = "dell";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.2";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.674.10892.5";
$config['os'][$os]['mib_dirs'][]            = "dell";
$config['os'][$os]['mibs'][]                = "DELL-RAC-MIB";

$os = "test_dlinkfw";
$config['os'][$os]['text']                  = "D-Link Firewall";
$config['os'][$os]['type']                  = "firewall";
$config['os'][$os]['vendor']                = "D-Link";
$config['os'][$os]['sysDescr'][]            = "/D-Link Firewall /";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.171.20.";
$config['os'][$os]['mib_dirs'][]            = "d-link";
$config['os'][$os]['mibs'][]                = "JUST-TEST-MIB";
$config['os'][$os]['model']                 = "d-link";

$os = "test_calix";
$config['os'][$os]['text']                  = "Calix";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "calix";
$config['os'][$os]['ifname']                  = 1;
#$config['os'][$os]['snmp']['max-rep']       = 15; // More - breaks, less or nobulk - very slow polling and discovery
$config['os'][$os]['graphs'][]              = "device_bits";
$config['os'][$os]['graphs'][]              = "device_processor";
$config['os'][$os]['graphs'][]              = "device_mempool";
$config['os'][$os]['sysObjectID'][]         = ".1.3.6.1.4.1.6321";
$config['os'][$os]['mibs'][]                = "E7-Calix-MIB";
$config['os'][$os]['model']                 = "calix"; // Per-HW hardware names

// EOF
