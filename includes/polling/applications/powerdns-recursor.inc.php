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

# <<<app-powerdns-recursor>>>
# all-outqueries  7371846
# dlg-only-drops  0
# dont-outqueries 32
# outgoing-timeouts       193883
# tcp-outqueries  8036
# throttled-out   39438
# throttled-outqueries    39438
# unreachables    12848
# answers-slow    170580
# answers0-1      549381
# answers1-10     692066
# answers10-100   2945477
# answers100-1000 1346860
# case-mismatches 0
# chain-resends   91977
# client-parse-errors     0
# edns-ping-matches       0
# edns-ping-mismatches    0
# ipv6-outqueries 818068
# no-packet-error 2596648
# noedns-outqueries       7379851
# noerror-answers 6718125
# noping-outqueries       0
# nsset-invalidations     2641
# nxdomain-answers        4369596
# over-capacity-drops     0
# qa-latency      33332
# questions       11097751
# resource-limits 2314
# server-parse-errors     0
# servfail-answers        10038
# spoof-prevents  0
# tcp-client-overflow     0
# tcp-questions   20830
# unauthorized-tcp        0
# unauthorized-udp        0
# unexpected-packets      0
# cache-entries   710696
# cache-hits      548700
# cache-misses    5155665
# concurrent-queries      1
# negcache-entries        45659
# nsspeeds-entries        3023
# packetcache-entries     271504
# packetcache-hits        5393402
# packetcache-misses      5683536
# sys-msec        1600408
# tcp-clients     0
# throttle-entries        56
# uptime  4231654
# user-msec       3423357

if (!empty($agent_data['app']['powerdns-recursor']))
{
  $app_id = discover_app($device, 'powerdns-recursor');

  foreach (explode("\n",$agent_data['app']['powerdns-recursor']) as $line)
  {
    list($key,$value) = explode("\t",$line,2);
    $powerdns_recursor[$key] = $value;
  }

  $rrd_filename = "app-powerdns-recursor-$app_id.rrd";

  unset($rrd_values);

  foreach (array('all-outqueries', 'dont-outqueries', 'tcp-outqueries', 'throttled-out', 'ipv6-outqueries', 'noedns-outqueries', 'noping-outqueries',
    'dlg-only-drops', 'over-capacity-drops', 'outgoing-timeouts', 'unreachables', 'answers-slow', 'answers0-1', 'answers1-10', 'answers10-100',
    'answers100-1000', 'noerror-answers', 'nxdomain-answers', 'servfail-answers', 'case-mismatches', 'chain-resends', 'client-parse-errors',
    'edns-ping-matches', 'edns-ping-mismatches', 'no-packet-error', 'nsset-invalidations', 'qa-latency', 'questions', 'resource-limits',
    'server-parse-errors', 'spoof-prevents', 'tcp-client-overflow', 'tcp-questions', 'unauthorized-tcp', 'unauthorized-udp', 'cache-entries',
    'cache-hits', 'cache-misses', 'negcache-entries', 'nsspeeds-entries', 'packetcache-entries', 'packetcache-hits', 'packetcache-misses',
    'unexpected-packets', 'concurrent-queries', 'tcp-clients', 'throttle-entries', 'uptime', 'sys-msec', 'user-msec') as $key)
  {
    $rrd_values[] = (is_numeric($powerdns_recursor[$key]) ? $powerdns_recursor[$key] : "U");
  }

  rrdtool_create($device, $rrd_filename, " DS:outQ_all:DERIVE:600:0:125000000000 \
        DS:outQ_dont:DERIVE:600:0:125000000000 \
        DS:outQ_tcp:DERIVE:600:0:125000000000 \
        DS:outQ_throttled:DERIVE:600:0:125000000000 \
        DS:outQ_ipv6:DERIVE:600:0:125000000000 \
        DS:outQ_noEDNS:DERIVE:600:0:125000000000 \
        DS:outQ_noPing:DERIVE:600:0:125000000000 \
        DS:drop_reqDlgOnly:DERIVE:600:0:125000000000 \
        DS:drop_overCap:DERIVE:600:0:125000000000 \
        DS:timeoutOutgoing:DERIVE:600:0:125000000000 \
        DS:unreachables:DERIVE:600:0:125000000000 \
        DS:answers_1s:DERIVE:600:0:125000000000 \
        DS:answers_1ms:DERIVE:600:0:125000000000 \
        DS:answers_10ms:DERIVE:600:0:125000000000 \
        DS:answers_100ms:DERIVE:600:0:125000000000 \
        DS:answers_1000ms:DERIVE:600:0:125000000000 \
        DS:answers_noerror:DERIVE:600:0:125000000000 \
        DS:answers_nxdomain:DERIVE:600:0:125000000000 \
        DS:answers_servfail:DERIVE:600:0:125000000000 \
        DS:caseMismatch:DERIVE:600:0:125000000000 \
        DS:chainResends:DERIVE:600:0:125000000000 \
        DS:clientParseErrors:DERIVE:600:0:125000000000 \
        DS:ednsPingMatch:DERIVE:600:0:125000000000 \
        DS:ednsPingMismatch:DERIVE:600:0:125000000000 \
        DS:noPacketError:DERIVE:600:0:125000000000 \
        DS:nssetInvalidations:DERIVE:600:0:125000000000 \
        DS:qaLatency:DERIVE:600:0:125000000000 \
        DS:questions:DERIVE:600:0:125000000000 \
        DS:resourceLimits:DERIVE:600:0:125000000000 \
        DS:serverParseErrors:DERIVE:600:0:125000000000 \
        DS:spoofPrevents:DERIVE:600:0:125000000000 \
        DS:tcpClientOverflow:DERIVE:600:0:125000000000 \
        DS:tcpQuestions:DERIVE:600:0:125000000000 \
        DS:tcpUnauthorized:DERIVE:600:0:125000000000 \
        DS:udpUnauthorized:DERIVE:600:0:125000000000 \
        DS:cacheEntries:DERIVE:600:0:125000000000 \
        DS:cacheHits:DERIVE:600:0:125000000000 \
        DS:cacheMisses:DERIVE:600:0:125000000000 \
        DS:negcacheEntries:DERIVE:600:0:125000000000 \
        DS:nsSpeedsEntries:DERIVE:600:0:125000000000 \
        DS:packetCacheEntries:DERIVE:600:0:125000000000 \
        DS:packetCacheHits:DERIVE:600:0:125000000000 \
        DS:packetCacheMisses:DERIVE:600:0:125000000000 \
        DS:unexpectedPkts:DERIVE:600:0:125000000000 \
        DS:concurrentQueries:DERIVE:600:0:125000000000 \
        DS:tcpClients:DERIVE:600:0:125000000000 \
        DS:throttleEntries:DERIVE:600:0:125000000000 \
        DS:uptime:DERIVE:600:0:125000000000 \
        DS:cpuTimeSys:DERIVE:600:0:125000000000 \
        DS:cpuTimeUser:DERIVE:600:0:125000000000 ");

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
}

// EOF
