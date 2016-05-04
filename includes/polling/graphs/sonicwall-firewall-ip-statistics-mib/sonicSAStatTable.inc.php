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

//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicIpsecSaIndex.3754384109 = Counter32: 10
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatPeerGateway.3754384109 = IpAddress: 173.15.223.9
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatSrcAddrBegin.3754384109 = IpAddress: 192.168.17.0
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatSrcAddrEnd.3754384109 = IpAddress: 192.168.17.255
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatDstAddrBegin.3754384109 = IpAddress: 192.168.16.0
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatDstAddrEnd.3754384109 = IpAddress: 192.168.16.255
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatCreateTime.3754384109 = STRING: 12/04/2014 00:32:11
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatEncryptPktCount.3754384109 = Counter32: 30261
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatEncryptByteCount.3754384109 = Counter32: 4702270
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatDecryptPktCount.3754384109 = Counter32: 32496
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatDecryptByteCount.3754384109 = Counter32: 5565365
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatInFragPktCount.3754384109 = Counter32: 0
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatOutFragPktCount.3754384109 = Counter32: 0
//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicSAStatUserName.3754384109 = STRING: Pestban - Woodstock

//FIXME. This table type currently unsupported, because collect_table() does not support multi-indexed tables
/*
$table_defs['SONICWALL-FIREWALL-IP-STATISTICS-MIB']['sonicSAStatTable'] = array(
  //'file'          => 'sonicwall-firewall-ip-statistics-mib_sonicsastattable.rrd', // auto-generated
  'mib'           => 'SONICWALL-FIREWALL-IP-STATISTICS-MIB',
  //'mib_dir'       => 'sonicwall',      // Already in OS definitions
  'table'         => 'sonicSAStatTable',
  'ds_rename'     => array('sonic' => '', 'SAStat' => ''),
  'graphs'        => array('sonicwall_sa_pkt', 'sonicwall_sa_byte'),
  'oids'          => array(
    'sonicSAStatEncryptPktCount'  => array('descr'  => 'Total encrypted packet count for this phase2 SA', 'ds_min' => '0'),
    'sonicSAStatEncryptByteCount' => array('descr'  => 'Total encrypted byte count for this phase2 SA', 'ds_min' => '0'),
    'sonicSAStatDecryptPktCount'  => array('descr'  => 'Total decrypted packet count for this phase2 SA', 'ds_min' => '0'),
    'sonicSAStatDecryptByteCount' => array('descr'  => 'Total decrypted byte count for this phase2 SA', 'ds_min' => '0'),
    'sonicSAStatInFragPktCount'   => array('descr'  => 'Incoming Fragmented packet count for this phase2 SA', 'ds_min' => '0'),
    'sonicSAStatOutFragPktCount'  => array('descr'  => 'Outgoing Fragmented packet count for this phase2 SA', 'ds_min' => '0'),
  )
);
*/

// EOF