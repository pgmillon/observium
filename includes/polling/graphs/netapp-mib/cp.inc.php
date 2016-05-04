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

/**
NETAPP-MIB::cpTime.0 = Timeticks: (5210983) 14:28:29.83
NETAPP-MIB::cpFromTimerOps.0 = Counter32: 452265
NETAPP-MIB::cpFromSnapshotOps.0 = Counter32: 0
NETAPP-MIB::cpFromLowWaterOps.0 = Counter32: 0
NETAPP-MIB::cpFromHighWaterOps.0 = Counter32: 126
NETAPP-MIB::cpFromLogFullOps.0 = Counter32: 0
NETAPP-MIB::cpFromCpOps.0 = Counter32: 29
NETAPP-MIB::cpTotalOps.0 = Counter32: 455593
NETAPP-MIB::cpFromFlushOps.0 = Counter32: 0
NETAPP-MIB::cpFromSyncOps.0 = Counter32: 3173
NETAPP-MIB::cpFromLowVbufOps.0 = Counter32: 0
NETAPP-MIB::cpFromCpDeferredOps.0 = Counter32: 0
NETAPP-MIB::cpFromLowDatavecsOps.0 = Counter32: 0
*/

$table_defs['NETAPP-MIB']['cp'] = array(
  'call_function' => 'snmp_get_multi',
  'mib'           => 'NETAPP-MIB',
  'mib_dir'       => 'netapp',
  'table'         => 'cp',
  'ds_rename'     => array(),
  'graphs'        => array('NETAPP-MIB_cp_ops'),
  'oids'          => array(
    'cpTime'               => array('descr'  => 'Time',           'ds_min' => '0'),
    'cpFromTimerOps'       => array('descr'  => 'Timer Ops',      'ds_min' => '0'),
    'cpFromSnapshotOps'    => array('descr'  => 'Snapshot Ops',   'ds_min' => '0'),
    'cpFromLowWaterOps'    => array('descr'  => 'Low Water Ops',  'ds_min' => '0'),
    'cpFromHighWaterOps'   => array('descr'  => 'High Water Ops', 'ds_min' => '0'),
    'cpFromLogFullOps'     => array('descr'  => 'Log Full Ops',   'ds_min' => '0'),
    'cpFromCpOps'          => array('descr'  => 'CP Ops',         'ds_min' => '0'),
    'cpTotalOps'           => array('descr'  => 'Total Ops',      'ds_min' => '0'),
    'cpFromFlushOps'       => array('descr'  => 'Flush Ops',      'ds_min' => '0'),
    'cpFromSyncOps'        => array('descr'  => 'Sync Ops',       'ds_min' => '0'),
    'cpFromLowVbufOps'     => array('descr'  => 'Low Vbuf Ops',   'ds_min' => '0'),
    'cpFromCpDeferredOps'  => array('descr'  => 'Cp Deferred Ops', 'ds_min' => '0'),
    'cpFromLowDatavecsOps' => array('descr'  => 'Low Datavecs Ops', 'ds_min' => '0'),
  )
);

?>