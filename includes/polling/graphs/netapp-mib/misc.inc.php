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

NETAPP-MIB::miscCacheAge.0 = INTEGER: 9139
NETAPP-MIB::misc64NfsOps.0 = Counter64: 80636003
NETAPP-MIB::misc64CifsOps.0 = Counter64: 15751
NETAPP-MIB::misc64HttpOps.0 = Counter64: 0
NETAPP-MIB::misc64NetRcvdBytes.0 = Counter64: 445066887085
NETAPP-MIB::misc64NetSentBytes.0 = Counter64: 443843064895
NETAPP-MIB::misc64DiskReadBytes.0 = Counter64: 48420194938880
NETAPP-MIB::misc64DiskWriteBytes.0 = Counter64: 1837467144192
NETAPP-MIB::misc64TapeReadBytes.0 = Counter64: 0
NETAPP-MIB::misc64TapeWriteBytes.0 = Counter64: 0

*/

$table_defs['NETAPP-MIB']['misc'] = array(
  'call_function' => 'snmp_get_multi',
  'mib'           => 'NETAPP-MIB',
  'mib_dir'       => 'netapp',
  'table'         => 'misc',
  'ds_rename'     => array('misc64' => '', 'misc' => ''),
  'graphs'        => array('NETAPP-MIB_misc_ops', 'NETAPP-MIB_disk_io', 'NETAPP-MIB_net_io', 'NETAPP-MIB_cache_age', 'NETAPP-MIB_tape_io'),
  'oids'          => array(
    'miscCacheAge'          => array('descr'  => 'Cache Age',         'ds_min' => '0', 'ds_type' => 'GAUGE'),
    'misc64NfsOps'          => array('descr'  => 'NFS Ops',           'ds_min' => '0'),
    'misc64CifsOps'         => array('descr'  => 'CIFS Ops',          'ds_min' => '0'),
    'misc64HttpOps'         => array('descr'  => 'HTTP Ops',          'ds_min' => '0'),
    'misc64NetRcvdBytes'    => array('descr'  => 'Network RX Bytes',  'ds_min' => '0'),
    'misc64NetSentBytes'    => array('descr'  => 'Network TX Bytes',  'ds_min' => '0'),
    'misc64DiskReadBytes'   => array('descr'  => 'Disk Read Bytes',   'ds_min' => '0'),
    'misc64DiskWriteBytes'  => array('descr'  => 'Disk Write Bytes',  'ds_min' => '0'),
    'misc64TapeReadBytes'   => array('descr'  => 'Tape Read Bytes',   'ds_min' => '0'),
    'misc64TapeWriteBytes'  => array('descr'  => 'Tape Write Bytes',  'ds_min' => '0'),
  )
);

?>