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

/*
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringFPCIndex.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringSPUIndex.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringCPUUsage.0 = Gauge32: 0 percent
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringMemoryUsage.0 = Gauge32: 57 percent
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringCurrentFlowSession.0 = Gauge32: 7
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringMaxFlowSession.0 = Gauge32: 524288
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringCurrentCPSession.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringMaxCPSession.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringNodeIndex.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringNodeDescr.0 = STRING: single
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringFlowSessIPv4.0 = Gauge32: 7
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringFlowSessIPv6.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringCPSessIPv4.0 = Gauge32: 0
JUNIPER-SRX5000-SPU-MONITORING-MIB::jnxJsSPUMonitoringCPSessIPv6.0 = Gauge32: 0
*/

$table_defs['JUNIPER-SRX5000-SPU-MONITORING-MIB']['jnxJsSPUMonitoring'] = array (
  'table'      => 'jnxJsSPUMonitoringObjectsTable',
  'numeric'    => '.1.3.6.1.4.1.2636.3.39.1.12.1.1.1',
  'mib'        => 'JUNIPER-SRX5000-SPU-MONITORING-MIB',
  'mib_dir'    => 'juniper',
  'descr'      => 'Juniper JunOS SRX 5000 SPU Monitoring',
  'graphs'     => array('jnxJsSPUMonitoringFlowSessions', 'jnxJsSPUMonitoringCPSessions'),
  'ds_rename'  => array('jnxJsSPUMonitoring' => ''),
  'oids'       => array(
    'jnxJsSPUMonitoringCurrentFlowSession'  =>  array('numeric' => '6',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringMaxFlowSession'      =>  array('numeric' => '7',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringCurrentCPSession'    =>  array('numeric' => '8',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringMaxCPSession'        =>  array('numeric' => '9',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringFlowSessIPv4'        =>  array('numeric' => '12', 'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringFlowSessIPv6'        =>  array('numeric' => '13', 'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringCPSessIPv4'          =>  array('numeric' => '14', 'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'jnxJsSPUMonitoringCPSessIPv6'          =>  array('numeric' => '15', 'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
  )
);

// EOF
