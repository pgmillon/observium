<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage tests
 * @copyright  (C) 2006-2015 Adam Armstrong
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

unset($config['os']['default']); // Override default for tests
$config['os']['default']['over'][0]['graph']        = "device_bits";
$config['os']['default']['over'][1]['graph']        = "device_uptime";
$config['os']['default']['over'][2]['graph']        = "device_ping";
// MIBs enabled for any os (except blacklisted mibs)
$config['os']['default']['mibs'][]                  = "ENTITY-MIB";
$config['os']['default']['mibs'][]                  = "ENTITY-SENSOR-MIB";
$config['os']['default']['mibs'][]                  = "HOST-RESOURCES-MIB";
$config['os']['default']['mibs'][]                  = "Q-BRIDGE-MIB";
$config['os']['default']['mibs'][]                  = "LLDP-MIB";
$config['os']['default']['mibs'][]                  = "UCD-SNMP-MIB";
//$config['os']['default']['mibs'][]                  = "CISCO-CDP-MIB"; // FIXME. See in module discovery-protocols

$os_group = "test_unix";
$config['os_group'][$os_group]['type']              = "server";
$config['os_group'][$os_group]['processor_stacked'] = 1;
$config['os_group'][$os_group]['over'][0]['graph']  = "device_processor";
$config['os_group'][$os_group]['over'][1]['graph']  = "device_ucd_memory";
//$config['os_group'][$os_group]['mibs'][]            = "UCD-SNMP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "LSI-MegaRAID-SAS-MIB";

$os_group = "test_cisco";
$config['os_group'][$os_group]['type']              = "network";
$config['os_group'][$os_group]['over'][0]['graph']  = "device_bits";
$config['os_group'][$os_group]['over'][1]['graph']  = "device_processor";
$config['os_group'][$os_group]['over'][2]['graph']  = "device_mempool";
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
$config['os_group'][$os_group]['over'][0]['graph']  = "device_bits";
$config['os_group'][$os_group]['over'][1]['graph']  = "device_processor";
$config['os_group'][$os_group]['over'][2]['graph']  = "device_mempool";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-IETF-IP-MIB";
$config['os_group'][$os_group]['mibs'][]            = "CISCO-ENTITY-SENSOR-MIB";
$config['os_group'][$os_group]['mib_blacklist'][]   = "Q-BRIDGE-MIB";

$os = "test_generic";
$config['os'][$os]['text']                  = "Generic Device";

// Linux-based OSes here please.

$os = "test_linux";
$config['os'][$os]['text']                  = "Linux";
$config['os'][$os]['type']                  = "server";
$config['os'][$os]['group']                 = "test_unix";
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
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
$config['os'][$os]['icon']                  = "cisco";

$os = "test_ciscosb";
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

$os = "test_junos";
$config['os'][$os]['text']                  = "Juniper JunOS";
$config['os'][$os]['type']                  = "network";
$config['os'][$os]['icon']                  = "juniper";
// $config['os'][$os]['snmp']['max-rep']       = 50; // Juniper is full of derp, this massively reduces performance.
$config['os'][$os]['over'][0]['graph']      = "device_bits";
$config['os'][$os]['over'][1]['graph']      = "device_processor";
$config['os'][$os]['over'][2]['graph']      = "device_mempool";
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

// EOF
