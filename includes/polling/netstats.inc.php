<?php

/**
 * Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

///FIXME. Be rewritten using collect_table()

$netstats_poll = array('ip' => array(), 'icmp' => array(), 'tcp' => array(), 'udp' => array(), 'snmp' => array()); // Init array
// IP
$netstats_poll['ip']['mib']    = 'IP-MIB';
$netstats_poll['ip']['graphs'] = array('netstat_ip', 'netstat_ip_frag');
$netstats_poll['ip']['oids_t'] = array('ipInReceives', 'ipOutRequests');
$netstats_poll['ip']['oids']   = array('ipForwDatagrams', 'ipInDelivers', 'ipInReceives', 'ipOutRequests', 'ipInDiscards',
                                       'ipOutDiscards', 'ipOutNoRoutes', 'ipReasmReqds', 'ipReasmOKs', 'ipReasmFails',
                                       'ipFragOKs', 'ipFragFails', 'ipFragCreates', 'ipInUnknownProtos', 'ipInHdrErrors', 'ipInAddrErrors');
// ICMP
$netstats_poll['icmp']['mib']    = 'IP-MIB';
$netstats_poll['icmp']['graphs'] = array('netstat_icmp', 'netstat_icmp_info');
$netstats_poll['icmp']['oids']   = array('icmpInMsgs', 'icmpOutMsgs', 'icmpInErrors', 'icmpOutErrors', 'icmpInEchos', 'icmpOutEchos',
                                         'icmpInEchoReps', 'icmpOutEchoReps', 'icmpInDestUnreachs', 'icmpOutDestUnreachs', 'icmpInParmProbs',
                                         'icmpInTimeExcds', 'icmpInSrcQuenchs', 'icmpInRedirects', 'icmpInTimestamps', 'icmpInTimestampReps',
                                         'icmpInAddrMasks', 'icmpInAddrMaskReps', 'icmpOutTimeExcds', 'icmpOutParmProbs', 'icmpOutSrcQuenchs',
                                         'icmpOutRedirects', 'icmpOutTimestamps', 'icmpOutTimestampReps', 'icmpOutAddrMasks', 'icmpOutAddrMaskReps');
// TCP
$netstats_poll['tcp']['mib']    = 'TCP-MIB';
$netstats_poll['tcp']['graphs'] = array('netstat_tcp_stats', 'netstat_tcp_segments', 'netstat_tcp_currestab');
$netstats_poll['tcp']['oids_t'] = array('tcpInSegs', 'tcpOutSegs');
$netstats_poll['tcp']['oids']   = array('tcpActiveOpens', 'tcpPassiveOpens', 'tcpAttemptFails', 'tcpEstabResets', 'tcpCurrEstab',
                                        'tcpInSegs', 'tcpOutSegs', 'tcpRetransSegs', 'tcpInErrs', 'tcpOutRsts');
                                      # 'tcpHCInSegs', 'tcpHCOutSegs' // ? Counter64 = 1,844674407 × 10^19
// UDP
$netstats_poll['udp']['mib']    = 'UDP-MIB';
$netstats_poll['udp']['graphs'] = array('netstat_udp_datagrams','netstat_udp_errors');
$netstats_poll['udp']['oids_t'] = array('udpInDatagrams', 'udpOutDatagrams');
$netstats_poll['udp']['oids']   = array('udpInDatagrams', 'udpOutDatagrams', 'udpInErrors', 'udpNoPorts');

// SNMP
$netstats_poll['snmp']['mib']    = 'SNMPv2-MIB';
$netstats_poll['snmp']['graphs'] = array('netstat_snmp_stats', 'netstat_snmp_packets');
$netstats_poll['snmp']['oids']   = array('snmpInPkts', 'snmpOutPkts', 'snmpInBadVersions', 'snmpInBadCommunityNames', 'snmpInBadCommunityUses',
                                         'snmpInASNParseErrs', 'snmpInTooBigs', 'snmpInNoSuchNames', 'snmpInBadValues', 'snmpInReadOnlys',
                                         'snmpInGenErrs', 'snmpInTotalReqVars', 'snmpInTotalSetVars', 'snmpInGetRequests', 'snmpInGetNexts',
                                         'snmpInSetRequests', 'snmpInGetResponses', 'snmpInTraps', 'snmpOutTooBigs', 'snmpOutNoSuchNames',
                                         'snmpOutBadValues', 'snmpOutGenErrs', 'snmpOutGetRequests', 'snmpOutGetNexts', 'snmpOutSetRequests',
                                         'snmpOutGetResponses', 'snmpOutTraps', 'snmpSilentDrops', 'snmpProxyDrops');
$mibs_blacklist = get_device_mibs_blacklist($device);

foreach ($netstats_poll as $type => $netstats)
{
  if (in_array($netstats['mib'], $mibs_blacklist)) { continue; } // Skip blacklisted MIBs

  $oids = $netstats['oids'];

  if (isset($netstats['oids_t']))
  {
    $oids_string = implode('.0 ', $netstats['oids_t']).'.0';
    $data = snmp_get_multi($device, $oids_string, '-OQUs', $netstats['mib'], mib_dirs()); // get testing oids
    if (!count($data)) { continue; }
    $data_array = $data[0];

    $oids_string = implode('.0 ', array_diff($oids, $netstats['oids_t'])).'.0';
    $data = snmp_get_multi($device, $oids_string, '-OQUs', $netstats['mib'], mib_dirs());
    $data_array = array_merge($data_array, $data[0]);
  } else {
    $data = snmpwalk_cache_oid($device, $type, array(), $netstats['mib'], mib_dirs());
    if (!count($data)) { continue; }
    $data_array = $data[0];
  }

  $rrd_file   = 'netstats-'.$type.'.rrd';
  $rrd_create = '';
  $rrd_update = 'N';
  foreach ($oids as $oid)
  {
    $oid_ds = truncate($oid, 19, '');
    if ($oid == 'tcpCurrEstab')
    {
      $rrd_create .= ' DS:'.$oid_ds.':GAUGE:600:U:4294967295';   // Gauge32 max value 2^32 = 4294967295
    } else {
      $rrd_create .= ' DS:'.$oid_ds.':COUNTER:600:U:4294967295'; // Counter32 max value 2^32 = 4294967295
    }

    $value = (is_numeric($data_array[$oid]) ? $data_array[$oid] : 'U');
    $rrd_update .= ':'.$value;
  }
  rrdtool_create($device, $rrd_file, $rrd_create);
  rrdtool_update($device, $rrd_file, $rrd_update);

  foreach ($netstats['graphs'] as $graph) { $graphs[$graph] = TRUE; }

  print_cli_data(nicecase($type)." Graphs", implode(" ", $netstats['graphs']), 2);
}

unset($netstats_poll, $netstats, $type, $oids, $oid, $oid_ds, $oids_string,
      $data, $data_array, $rrd_create, $rrd_file, $rrd_update, $value, $mibs_blacklist);

// EOF
