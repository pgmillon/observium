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

$table_defs['FIREBRICK-MIB']['fbL2tpTunnelStats'] = array (
  'table'         => 'fbL2tpTunnelStats',
  'numeric'       => '3.6.1.4.1.24693.1701.1',
  'call_function' => 'snmp_get_multi',
  'mib'           => 'FIREBRICK-MIB',
  'mib_dir'       => 'firebrick',
  'descr'         => 'Firebrick L2TP Tunnel Statistics',
  'graphs'        => array('fbL2tpTunnelStats'),
  'ds_rename'     => array('fbL2tpTunnels' => ''),
  'no_index'      => TRUE,
  'oids'          => array(
     'fbL2tpTunnelsFree'    =>  array('numeric' => '0',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpTunnelsOpening' =>  array('numeric' => '1',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpTunnelsLive'    =>  array('numeric' => '2',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpTunnelsClosing' =>  array('numeric' => '3',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpTunnelsFailed'  =>  array('numeric' => '4',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpTunnelsClosed'  =>  array('numeric' => '5',  'descr' => '', 'ds_type' => 'GAUGE', 'ds_min' => '0')
  )
);

?>

