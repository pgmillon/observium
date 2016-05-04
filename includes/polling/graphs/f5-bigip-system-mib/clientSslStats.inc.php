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

// F5-BIGIP-SYSTEM-MIB sysGlobalClientSslStat
//
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatCurConns.0 = Counter64: 2376
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatMaxConns.0 = Counter64: 10360
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatCurNativeConns.0 = Counter64: 2351
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatMaxNativeConns.0 = Counter64: 10252
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotNativeConns.0 = Counter64: 241684205
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatCurCompatConns.0 = Counter64: 0
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatMaxCompatConns.0 = Counter64: 0
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatTotCompatConns.0 = Counter64: 0
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatSslv2.0 = Counter64: 0
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatSslv3.0 = Counter64: 81589
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatTlsv1.0 = Counter64: 53907948
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatTlsv11.0 = Counter64: 2800169
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatTlsv12.0 = Counter64: 184894499
// F5-BIGIP-SYSTEM-MIB::sysClientsslStatDtlsv1.0 = Counter64: 0

$table_defs['F5-BIGIP-SYSTEM-MIB']['clientssl'] = array(
  'file'          => 'clientssl.rrd',
  'call_function' => 'snmp_get',
  'mib'           => 'F5-BIGIP-SYSTEM-MIB',
  'mib_dir'       => 'f5',
  'table'         => 'sysGlobalClientSslStat',
  'ds_rename'     => array(
     'sysClientsslStat' => '',
  ),
  'graphs'        => array('f5_clientssl_conns', 'f5_clientssl_vers'),
  'oids'          => array(
     'sysClientsslStatCurConns' => array('descr' => 'Current Connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'sysClientsslStatCurNativeConns' => array('descr' => 'Current Native Connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'sysClientsslStatCurCompatConns' => array('descr' => 'Current Compat Connections', 'ds_type' => 'GAUGE', 'ds_min' => '0'),
     'sysClientsslStatSslv2'    => array('descr' => 'Current SSLv2 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'sysClientsslStatSslv3'    => array('descr' => 'Current SSLv3 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'sysClientsslStatTlsv1'    => array('descr' => 'Current TLSv1.0 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'sysClientsslStatTlsv11'   => array('descr' => 'Current TLSv1.1 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'sysClientsslStatTlsv12'   => array('descr' => 'Current TLSv1.2 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
     'sysClientsslStatDtlsv1'   => array('descr' => 'Current DTLSv1 Connections', 'ds_type' => 'COUNTER', 'ds_min' => '0'),
  )
);

// EOF
