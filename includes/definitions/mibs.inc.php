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

// FIXME - format perhaps sucks
// Maybe some kind of web-based interface for managing this would be better. I don't know. :D

$mib = 'RUCKUS-SYSTEM-MIB';
$config['mibs'][$mib]['mib_dir'] = "ruckus";
$config['mibs'][$mib]['descr']   = "Ruckus Wireless system MIB containing resource utilisation and system configuration.";
$config['mibs'][$mib]['processor']['ruckusSystemCPUUtil'] = array('type'     => 'static',
                                                                  'descr'    => 'System CPU',
                                                                  'oid'      => 'ruckusSystemCPUUtil.0',
                                                                  'oid_num'  => '1.3.6.1.4.1.25053.1.1.11.1.1.1.1.0');

$mib = 'A10-AX-MIB';
$config['mibs'][$mib]['mib_dir'] = "a10";
$config['mibs'][$mib]['descr']   = "Management MIB for A10 application acceleration appliance";
$config['mibs'][$mib]['processor']['axSysCpuTable']       = array('type'     => 'table',
                                                                  // 'descr'    => 'CPU %%INDEX%%', // This is the default
                                                                  'table'    => 'axSysCpuTable',
                                                                  'oid'      => 'axSysCpuUsageValue',
                                                                  'oid_num'  => '.1.3.6.1.4.1.22610.2.4.1.3.2.1.3');

$config['mibs'][$mib]['sensor']['axSysHwPhySystemTemp'] =    array('type'    => 'static',
                                                                  'descr'    => 'System Temperature',
                                                                  'class'    => 'temperature',
                                                                  'measured' => 'device',
                                                                  'oid'      => 'axSysHwPhySystemTemp.0',
                                                                  'oid_num'  => '.1.3.6.1.4.1.22610.2.4.1.5.1.0');

$config['mibs'][$mib]['sensor']['axSysHwFan1Speed']       = array('type'     => 'static',
                                                                  'descr'    => 'System Fan 1',
                                                                  'class'    => 'fanspeed',
                                                                  'measured' => 'device',
                                                                  'oid'      => 'axSysHwFan1Speed.0',
                                                                  'oid_num'  => '.1.3.6.1.4.1.22610.2.4.1.5.2.0');

$config['mibs'][$mib]['sensor']['axSysHwFan2Speed']       = array('type'     => 'static',
                                                                  'descr'    => 'System Fan 2',
                                                                  'class'    => 'fanspeed',
                                                                  'measured' => 'device',
                                                                  'oid'      => 'axSysHwFan2Speed.0',
                                                                  'oid_num'  => '.1.3.6.1.4.1.22610.2.4.1.5.3.0');

$config['mibs'][$mib]['sensor']['axSysHwFan3Speed']       = array('type'     => 'static',
                                                                  'descr'    => 'System Fan 3',
                                                                  'class'    => 'fanspeed',
                                                                  'measured' => 'device',
                                                                  'oid'      => 'axSysHwFan3Speed.0',
                                                                  'oid_num'  => '.1.3.6.1.4.1.22610.2.4.1.5.4.0');
