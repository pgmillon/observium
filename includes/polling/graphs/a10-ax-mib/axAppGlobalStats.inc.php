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

// enterprises.a10.a10Mgmt.axMgmt.axApp.axAppGlobals

// A10-AX-MIB::axAppGlobalTotalCurrentConnections.0 = Counter64: 36
// A10-AX-MIB::axAppGlobalTotalNewConnections.0 = Counter64: 76035548
// A10-AX-MIB::axAppGlobalTotalNewL4Connections.0 = Counter64: 76035548
// A10-AX-MIB::axAppGlobalTotalNewL7Connections.0 = Counter64: 0
// A10-AX-MIB::axAppGlobalTotalNewIPNatConnections.0 = Counter64: 0
// A10-AX-MIB::axAppGlobalTotalSSLConnections.0 = Counter64: 0
// A10-AX-MIB::axAppGlobalTotalL7Requests.0 = Counter64: 0

// A10-AX-MIB::axAppGlobalBufferConfigLimit.0 = INTEGER: 90000
// A10-AX-MIB::axAppGlobalBufferCurrentUsage.0 = INTEGER: 13416

// .1.3.6.1.4.1.22610.2.4.3.1.2.1.0 = Counter64: 37
// .1.3.6.1.4.1.22610.2.4.3.1.2.2.0 = Counter64: 80370403
// .1.3.6.1.4.1.22610.2.4.3.1.2.3.0 = Counter64: 80370403
// .1.3.6.1.4.1.22610.2.4.3.1.2.4.0 = Counter64: 0
// .1.3.6.1.4.1.22610.2.4.3.1.2.5.0 = Counter64: 0
// .1.3.6.1.4.1.22610.2.4.3.1.2.6.0 = Counter64: 0
// .1.3.6.1.4.1.22610.2.4.3.1.2.7.0 = Counter64: 0

// .1.3.6.1.4.1.22610.2.4.3.1.3.1.0 = INTEGER: 90000
// .1.3.6.1.4.1.22610.2.4.3.1.3.2.0 = INTEGER: 9849

$table_defs['NS-ROOT-MIB']['axAppGlobalStats'] = array (
  'table'      => 'axAppGlobals',
  'numeric'    => '.1.3.6.1.4.1.22610.2.4.3.1.2',
  'mib'        => 'A10-AX-MIB',
  'mib_dir'    => 'a10',
//  'file'       => 'axAppGlobalStats.rrd',
  'descr'      => 'A10 Global Statistics',
  'graphs'     => array('axAppGlobalCurConns', 'axAppGlobalTotConns', 'axAppTotL7Requests', 'axAppGlobalBuffers'),
  'ds_rename'  => array('axAppGlobal' => '', 'Connections' => 'Conns'),
  'oids'       => array(
    'axAppGlobalTotalCurrentConnections'  =>  array('numeric' => '1',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
    'axAppGlobalTotalNewConnections'      =>  array('numeric' => '2',  'descr' => ''),
    'axAppGlobalTotalNewL4Connections'    =>  array('numeric' => '3',  'descr' => ''),
    'axAppGlobalTotalNewL7Connections'    =>  array('numeric' => '4',  'descr' => ''),
    'axAppGlobalTotalNewIPNatConnections' =>  array('numeric' => '5',  'descr' => ''),
    'axAppGlobalTotalSSLConnections'      =>  array('numeric' => '6',  'descr' => ''),
    'axAppGlobalTotalL7Requests'          =>  array('numeric' => '7',  'descr' => ''),
    'axAppGlobalBufferConfigLimit'        =>  array('numeric' => '6',  'descr' => '', 'ds_type' => 'GAUGE'),
    'axAppGlobalBufferCurrentUsage'       =>  array('numeric' => '7',  'descr' => '', 'ds_type' => 'GAUGE'),
  )
);

?>
