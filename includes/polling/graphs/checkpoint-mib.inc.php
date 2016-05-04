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

//CHECKPOINT-MIB::fwNumConn.0 = 169
//CHECKPOINT-MIB::fwPeakNumConn.0 = 1409
//CHECKPOINT-MIB::fwPacketsRate.0 = 18297
//CHECKPOINT-MIB::fwAcceptedBytesTotalRate.0 = 2855057
//CHECKPOINT-MIB::fwDroppedBytesTotalRate.0 = 2017
//CHECKPOINT-MIB::fwDroppedTotalRate.0 = 22
//CHECKPOINT-MIB::fwAccepted.0 = INTEGER: 4492083
//CHECKPOINT-MIB::fwRejected.0 = INTEGER: 0
//CHECKPOINT-MIB::fwDropped.0 = INTEGER: 179317
//CHECKPOINT-MIB::fwLogged.0 = INTEGER: 98061
// These are from the root of fwPolicyStat

$table_defs['CHECKPOINT-MIB']['fw'] = array(
  //'file'          => 'checkpoint-mib_fw.rrd',
  'call_function' => 'snmp_get_multi',
  'mib'           => 'CHECKPOINT-MIB',
  'mib_dir'       => 'checkpoint',
  'table'         => 'fw', // Table fw super-global, use snmp_get_multi()
  'ds_rename'     => array('fw' => '', 'Total' => ''),
  'graphs'        => array('checkpoint_connections', 'checkpoint_packets'),
  'oids'          => array(
    'fwNumConn'                => array('descr'  => 'Number of connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwPeakNumConn'            => array('descr'  => 'Peak number of connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    // I don't know why chekpoint put this oids into table fwPolicyStat
    'fwPacketsRate'            => array('descr'  => 'packets rate', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwAcceptedBytesTotalRate' => array('descr'  => 'dropped packets rate', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwDroppedBytesTotalRate'  => array('descr'  => 'bytes thruput', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwDroppedTotalRate'       => array('descr'  => 'dropped bytes rate', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwAccepted'               => array('descr'  => 'The number of accepted packets', 'ds_min' => '0'),
    'fwRejected'               => array('descr'  => 'The number of rejected  packets', 'ds_min' => '0'),
    'fwDropped'                => array('descr'  => 'The number of dropped packets', 'ds_min' => '0'),
    'fwLogged'                 => array('descr'  => 'The number of logged packets', 'ds_min' => '0')
  )
);

//CHECKPOINT-MIB::cpvIKECurrSAs.0 = 2
//CHECKPOINT-MIB::cpvIKECurrInitSAs.0 = 1
//CHECKPOINT-MIB::cpvIKECurrRespSAs.0 = 1
//CHECKPOINT-MIB::cpvIKETotalSAs.0 = 2643
//CHECKPOINT-MIB::cpvIKETotalInitSAs.0 = 96
//CHECKPOINT-MIB::cpvIKETotalRespSAs.0 = 2547
//CHECKPOINT-MIB::cpvIKETotalSAsAttempts.0 = 2
//CHECKPOINT-MIB::cpvIKETotalSAsInitAttempts.0 = 1
//CHECKPOINT-MIB::cpvIKETotalSAsRespAttempts.0 = 1
//CHECKPOINT-MIB::cpvIKEMaxConncurSAs.0 = 10
//CHECKPOINT-MIB::cpvIKEMaxConncurInitSAs.0 = 2
//CHECKPOINT-MIB::cpvIKEMaxConncurRespSAs.0 = 10
// These are from the root of fw

$table_defs['CHECKPOINT-MIB']['cpvIKEglobals'] = array(
  //'file'     => 'checkpoint-mib_cpvikeglobals.rrd',
  'mib'       => 'CHECKPOINT-MIB',
  'mib_dir'   => 'checkpoint',
  'table'     => 'cpvIKEglobals',
  'ds_rename' => array('cpv' => '', 'Attempts' => 'Att'),
  'graphs'    => array('checkpoint_vpn_sa'),
  'oids'      => array(
    'cpvIKECurrSAs'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKECurrInitSAs'           => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKECurrRespSAs'           => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalSAs'              => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalInitSAs'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalRespSAs'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalSAsAttempts'      => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalSAsInitAttempts'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKETotalSAsRespAttempts'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKEMaxConncurSAs'         => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKEMaxConncurInitSAs'     => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvIKEMaxConncurRespSAs'     => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
  )
);

//CHECKPOINT-MIB::cpvCurrEspSAsIn.0 = 2
//CHECKPOINT-MIB::cpvTotalEspSAsIn.0 = 19242
//CHECKPOINT-MIB::cpvCurrEspSAsOut.0 = 1
//CHECKPOINT-MIB::cpvTotalEspSAsOut.0 = 19242
//CHECKPOINT-MIB::cpvMaxConncurEspSAsIn.0 = 8
//CHECKPOINT-MIB::cpvMaxConncurEspSAsOut.0 = 2
// These are from cpvIKEGlobals, cpvSaStatistics and cpvStatistics

$table_defs['CHECKPOINT-MIB']['cpvSaStatistics'] = array(
  //'file'     => 'checkpoint-mib_cpvsastatistics.rrd',
  'mib'       => 'CHECKPOINT-MIB',
  'mib_dir'   => 'checkpoint',
  'table'     => 'cpvSaStatistics',
  'ds_rename' => array('cpv' => ''),
  'graphs'    => array('checkpoint_vpn_sa'),
  'oids'      => array(
    'cpvCurrEspSAsIn'         => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvTotalEspSAsIn'        => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvCurrEspSAsOut'        => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvTotalEspSAsOut'       => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvCurrAhSAsIn'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvTotalAhSAsIn'         => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvCurrAhSAsOut'         => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvTotalAhSAsOut'        => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvMaxConncurEspSAsIn'   => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvMaxConncurEspSAsOut'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvMaxConncurAhSAsIn'    => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'cpvMaxConncurAhSAsOut'   => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
  )
);

//CHECKPOINT-MIB::cpvEncPackets.0 = 259540277
//CHECKPOINT-MIB::cpvDecPackets.0 = 369845461
//CHECKPOINT-MIB::cpvErrOut.0 = 1
//CHECKPOINT-MIB::cpvErrIn.0 = 27
//CHECKPOINT-MIB::cpvErrIke.0 = 1
//CHECKPOINT-MIB::cpvErrPolicy.0 = 1

$table_defs['CHECKPOINT-MIB']['cpvGeneral'] = array(
  //'file'     => 'checkpoint-mib_cpvgeneral.rrd',
  'mib'       => 'CHECKPOINT-MIB',
  'mib_dir'   => 'checkpoint',
  'table'     => 'cpvGeneral',
  'ds_rename' => array('cpv' => ''),
  'graphs'    => array('checkpoint_vpn_packets'),
  'oids'      => array(
    'cpvEncPackets'   => array('descr' => 'Encrypted packets'),
    'cpvDecPackets'   => array('descr' => 'Decrypted packets'),
    'cpvErrOut'       => array('descr' => 'Encryption errors'),
    'cpvErrIn'        => array('descr' => 'Decryption errors'),
    'cpvErrIke'       => array('descr' => 'IKE errors'),
    'cpvErrPolicy'    => array('descr' => 'Policy errors'),
  )
);

//CHECKPOINT-MIB::fwKmem-system-physical-mem.0 = 0
//CHECKPOINT-MIB::fwKmem-available-physical-mem.0 = 0
//CHECKPOINT-MIB::fwKmem-aix-heap-size.0 = 0
//CHECKPOINT-MIB::fwKmem-bytes-used.0 = 80705044
//CHECKPOINT-MIB::fwKmem-blocking-bytes-used.0 = 352024
//CHECKPOINT-MIB::fwKmem-non-blocking-bytes-used.0 = 80353020
//CHECKPOINT-MIB::fwKmem-bytes-unused.0 = 0
//CHECKPOINT-MIB::fwKmem-bytes-peak.0 = 88392176
//CHECKPOINT-MIB::fwKmem-blocking-bytes-peak.0 = 461708
//CHECKPOINT-MIB::fwKmem-non-blocking-bytes-peak.0 = 87930468
//CHECKPOINT-MIB::fwKmem-bytes-internal-use.0 = 11136
//CHECKPOINT-MIB::fwKmem-number-of-items.0 = 696
//CHECKPOINT-MIB::fwKmem-alloc-operations.0 = 942582
//CHECKPOINT-MIB::fwKmem-free-operations.0 = 941886
//CHECKPOINT-MIB::fwKmem-failed-alloc.0 = 0
//CHECKPOINT-MIB::fwKmem-failed-free.0 = 0

$table_defs['CHECKPOINT-MIB']['fwKmem'] = array(
  //'file'      => 'checkpoint-mib_fwkmem.rrd',
  'mib'       => 'CHECKPOINT-MIB',
  'mib_dir'   => 'checkpoint',
  'table'     => 'fwKmem',
  'ds_rename' => array('fw' => '', 'blocking' => 'blk', 'alloc' => 'alc',
                       'physical' => 'phy', 'non' => 'n', 'internal' => 'int'),
  'graphs'   => array('checkpoint_memory', 'checkpoint_memory_operations'),
  'oids'     => array(
    'fwKmem-system-physical-mem'      => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-available-physical-mem'   => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-aix-heap-size'            => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-bytes-used'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-blocking-bytes-used'      => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-non-blocking-bytes-used'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-bytes-unused'             => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-bytes-peak'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-blocking-bytes-peak'      => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-non-blocking-bytes-peak'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-bytes-internal-use'       => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-number-of-items'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwKmem-alloc-operations'         => array(),
    'fwKmem-free-operations'          => array(),
    'fwKmem-failed-alloc'             => array(),
    'fwKmem-failed-free'              => array(),
  )
);

//CHECKPOINT-MIB::fwHmem-block-size.0 = 4096
//CHECKPOINT-MIB::fwHmem-requested-bytes.0 = 20971520
//CHECKPOINT-MIB::fwHmem-initial-allocated-bytes.0 = 20971520
//CHECKPOINT-MIB::fwHmem-initial-allocated-blocks.0 = 0
//CHECKPOINT-MIB::fwHmem-initial-allocated-pools.0 = 0
//CHECKPOINT-MIB::fwHmem-current-allocated-bytes.0 = 38947108
//CHECKPOINT-MIB::fwHmem-current-allocated-blocks.0 = 9508
//CHECKPOINT-MIB::fwHmem-current-allocated-pools.0 = 3
//CHECKPOINT-MIB::fwHmem-maximum-bytes.0 = 83886080
//CHECKPOINT-MIB::fwHmem-maximum-pools.0 = 10
//CHECKPOINT-MIB::fwHmem-bytes-used.0 = 17387232
//CHECKPOINT-MIB::fwHmem-blocks-used.0 = 4565
//CHECKPOINT-MIB::fwHmem-bytes-unused.0 = 21559876
//CHECKPOINT-MIB::fwHmem-blocks-unused.0 = 4943
//CHECKPOINT-MIB::fwHmem-bytes-peak.0 = 33557112
//CHECKPOINT-MIB::fwHmem-blocks-peak.0 = 8286
//CHECKPOINT-MIB::fwHmem-bytes-internal-use.0 = 111900
//CHECKPOINT-MIB::fwHmem-number-of-items.0 = 99333
//CHECKPOINT-MIB::fwHmem-alloc-operations.0 = 1380168471
//CHECKPOINT-MIB::fwHmem-free-operations.0 = 1380069138
//CHECKPOINT-MIB::fwHmem-failed-alloc.0 = 3376
//CHECKPOINT-MIB::fwHmem-failed-free.0 = 0
// These are from fwHmem and fwKmem

$table_defs['CHECKPOINT-MIB']['fwHmem'] = array(
  //'file'      => 'checkpoint-mib_fwhmem.rrd',
  'mib'       => 'CHECKPOINT-MIB',
  'mib_dir'   => 'checkpoint',
  'table'     => 'fwHmem',
  'ds_rename' => array('fw' => '', 'allocated' => 'alc', 'alloc' => 'alc', 'available' => 'avai',
                       'current' => 'cur', 'initial' => 'ini', 'internal' => 'int'),
  'graphs'   => array('checkpoint_memory', 'checkpoint_memory_operations'),
  'oids'     => array(
    'fwHmem-block-size'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-requested-bytes'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-initial-allocated-bytes'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-initial-allocated-blocks' => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-initial-allocated-pools'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-current-allocated-bytes'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-current-allocated-blocks' => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-current-allocated-pools'  => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-maximum-bytes'            => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-maximum-pools'            => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-bytes-used'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-blocks-used'              => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-bytes-unused'             => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-blocks-unused'            => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-bytes-peak'               => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-blocks-peak'              => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-bytes-internal-use'       => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-number-of-items'          => array('ds_type' => 'GAUGE', 'ds_min' => '0'),
    'fwHmem-alloc-operations'         => array(),
    'fwHmem-free-operations'          => array(),
    'fwHmem-failed-alloc'             => array(),
    'fwHmem-failed-free'              => array(),
  )
);

// EOF
