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

$table_defs['FIREBRICK-MIB']['fbL2tpSessionStats'] = array (
  'table'         => 'fbL2tpSessionStats',
  'numeric'       => '3.6.1.4.1.24693.1701.1701.2',
  'call_function' => 'snmp_get_multi',
  'mib'           => 'FIREBRICK-MIB',
  'mib_dir'       => 'firebrick',
  'descr'         => 'Firebrick L2TP Session Statistics',
  'graphs'        => array('fbL2tpSessionStats'),
  'ds_rename'     => array('fbL2tpSessions' => ''),
  'no_index'      => TRUE,
  'oids'          => array(
     'fbL2tpSessionsFree'        =>  array('numeric' => '0',  'descr' => 'Sessions in state FREE',         'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsWaiting'     =>  array('numeric' => '1',  'descr' => 'Sessions in state WAITING',      'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsOpening'     =>  array('numeric' => '2',  'descr' => 'Sessions in state OPENING',      'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsNegotiating' =>  array('numeric' => '3',  'descr' => 'Sessions in state NEGOTIATING',  'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsAuthPending' =>  array('numeric' => '4',  'descr' => 'Sessions in state AUTH-PENDING', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsStarted'     =>  array('numeric' => '5',  'descr' => 'Sessions in state STARTED',      'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsLive'        =>  array('numeric' => '6',  'descr' => 'Sessions in state LIVE',         'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsAcctPending' =>  array('numeric' => '7',  'descr' => 'Sessions in state ACCT-PENDING', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsClosing'     =>  array('numeric' => '8',  'descr' => 'Sessions in state CLOSING',      'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'fbL2tpSessionsClosed'      =>  array('numeric' => '9',  'descr' => 'Sessions in state CLOSED',       'ds_type' => 'GAUGE', 'ds_min' => '0'),
  )
);

?>
