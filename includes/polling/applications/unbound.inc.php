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

/*
thread0.num.queries=2231
thread0.num.cachehits=2098
thread0.num.cachemiss=133
thread0.num.prefetch=0
thread0.num.recursivereplies=133
thread0.requestlist.avg=1.78195
thread0.requestlist.max=43
thread0.requestlist.overwritten=0
thread0.requestlist.exceeded=0
thread0.requestlist.current.all=0
thread0.requestlist.current.user=0
thread0.recursion.time.avg=0.130315
thread0.recursion.time.median=0.0065024
total.num.queries=2231
total.num.cachehits=2098
total.num.cachemiss=133
total.num.prefetch=0
total.num.recursivereplies=133
total.requestlist.avg=1.78195
total.requestlist.max=43
total.requestlist.overwritten=0
total.requestlist.exceeded=0
total.requestlist.current.all=0
total.requestlist.current.user=0
total.recursion.time.avg=0.130315
total.recursion.time.median=0.0065024
time.now=1345738158.409360
time.up=129.622280
time.elapsed=6.775663
mem.total.sbrk=7561216
mem.cache.rrset=293070
mem.cache.message=158049
mem.mod.iterator=16532
mem.mod.validator=116833
histogram.000000.000000.to.000000.000001=3
histogram.000000.000001.to.000000.000002=0
histogram.000000.000002.to.000000.000004=0
histogram.000000.000004.to.000000.000008=0
histogram.000000.000008.to.000000.000016=0
histogram.000000.000016.to.000000.000032=0
histogram.000000.000032.to.000000.000064=0
histogram.000000.000064.to.000000.000128=0
histogram.000000.000128.to.000000.000256=0
histogram.000000.000256.to.000000.000512=0
histogram.000000.000512.to.000000.001024=4
histogram.000000.001024.to.000000.002048=39
histogram.000000.002048.to.000000.004096=4
histogram.000000.004096.to.000000.008192=46
histogram.000000.008192.to.000000.016384=17
histogram.000000.016384.to.000000.032768=6
histogram.000000.032768.to.000000.065536=0
histogram.000000.065536.to.000000.131072=2
histogram.000000.131072.to.000000.262144=7
histogram.000000.262144.to.000000.524288=10
histogram.000000.524288.to.000001.000000=10
histogram.000001.000000.to.000002.000000=4
histogram.000002.000000.to.000004.000000=0
histogram.000004.000000.to.000008.000000=0
histogram.000008.000000.to.000016.000000=0
histogram.000016.000000.to.000032.000000=0
histogram.000032.000000.to.000064.000000=0
histogram.000064.000000.to.000128.000000=0
histogram.000128.000000.to.000256.000000=0
histogram.000256.000000.to.000512.000000=0
histogram.000512.000000.to.001024.000000=0
histogram.001024.000000.to.002048.000000=0
histogram.002048.000000.to.004096.000000=0
histogram.004096.000000.to.008192.000000=0
histogram.008192.000000.to.016384.000000=0
histogram.016384.000000.to.032768.000000=0
histogram.032768.000000.to.065536.000000=0
histogram.065536.000000.to.131072.000000=0
histogram.131072.000000.to.262144.000000=0
histogram.262144.000000.to.524288.000000=0
num.query.type.A=2515
num.query.type.PTR=105
num.query.type.MX=3
num.query.type.AAAA=165
num.query.type.SRV=2
num.query.class.IN=2790
num.query.opcode.QUERY=2790
num.query.tcp=0
num.query.ipv6=0
num.query.flags.QR=0
num.query.flags.AA=0
num.query.flags.TC=0
num.query.flags.RD=2790
num.query.flags.RA=0
num.query.flags.Z=0
num.query.flags.AD=0
num.query.flags.CD=0
num.query.edns.present=0
num.query.edns.DO=0
num.answer.rcode.NOERROR=2778
num.answer.rcode.NXDOMAIN=12
num.answer.rcode.nodata=128
num.answer.secure=2
num.answer.bogus=0
num.rrset.bogus=0
unwanted.queries=0
unwanted.replies=0
*/

if (!empty($agent_data['app']['unbound']))
{
  $app_id = discover_app($device, 'unbound');

  foreach (explode("\n",$agent_data['app']['unbound']) as $line)
  {
    list($key,$value) = explode("=",$line,2);
    $unbound[$key] = $value;
  }

  while (1)
  {
    if (!isset($threadnum))
    {
      $thread = "total";
      $threadnum = -1; # Incremented below, we want to check thread0 next, so we put this to -1. Yes, ugly... ;-)
    }
    else
    {
      $thread = "thread" . $threadnum;
    }

    if (isset($unbound["$thread.num.queries"]))
    {
      $rrd_filename = "app-unbound-$app_id-$thread.rrd";

      rrdtool_create($device, $rrd_filename, " \
          DS:numQueries:DERIVE:600:0:125000000000 \
          DS:cacheHits:DERIVE:600:0:125000000000 \
          DS:cacheMiss:DERIVE:600:0:125000000000 \
          DS:prefetch:DERIVE:600:0:125000000000 \
          DS:recursiveReplies:DERIVE:600:0:125000000000 \
          DS:reqListAvg:GAUGE:600:0:125000000000 \
          DS:reqListMax:GAUGE:600:0:125000000000 \
          DS:reqListOverwritten:GAUGE:600:0:125000000000 \
          DS:reqListExceeded:GAUGE:600:0:125000000000 \
          DS:reqListCurrentAll:GAUGE:600:0:125000000000 \
          DS:reqListCurrentUser:GAUGE:600:0:125000000000 \
          DS:recursionTimeAvg:GAUGE:600:0:125000000000 \
          DS:recursionTimeMedian:GAUGE:600:0:125000000000 ");

      foreach (array("$thread.num.queries","$thread.num.cachehits","$thread.num.cachemiss","$thread.num.prefetch","$thread.num.recursivereplies","$thread.requestlist.avg",
        "$thread.requestlist.max","$thread.requestlist.overwritten","$thread.requestlist.exceeded","$thread.requestlist.current.all","$thread.requestlist.current.user",
        "$thread.recursion.time.avg","$thread.recursion.time.median") as $key)
      {
        $rrd_values[] = (is_numeric($unbound[$key]) ? round($unbound[$key]) : "U"); # RRDtool doesn't like non-integer, so we round here. Not nice :-(
      }

      rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
      unset($rrd_values);

      $threadnum++;
    }
    else
    {
      break;
    }
  }

  unset($threadnum);

  $rrd_filename = "app-unbound-$app_id-memory.rrd";

  rrdtool_create($device, $rrd_filename, " \
      DS:memTotal:GAUGE:600:0:125000000000 \
      DS:memCacheRRset:GAUGE:600:0:125000000000 \
      DS:memCacheMessage:GAUGE:600:0:125000000000 \
      DS:memModIterator:GAUGE:600:0:125000000000 \
      DS:memModValidator:GAUGE:600:0:125000000000 ");

  foreach (array("mem.total.sbrk","mem.cache.rrset","mem.cache.message","mem.mod.iterator","mem.mod.validator") as $key)
  {
    $rrd_values[] = (is_numeric($unbound[$key]) ? $unbound[$key] : "U");
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  unset($rrd_values);

  $rrd_filename = "app-unbound-$app_id-queries.rrd";

  $dns_qtype = array('A', 'A6', 'AAAA', 'AFSDB', 'ANY', 'APL', 'ATMA', 'AXFR', 'CERT', 'CNAME', 'DHCID', 'DLV', 'DNAME', 'DNSKEY', 'DS', 'EID', 'GID',
    'GPOS', 'HINFO', 'IPSECKEY', 'ISDN', 'IXFR', 'KEY', 'KX', 'LOC', 'MAILA', 'MAILB', 'MB', 'MD', 'MF', 'MG', 'MINFO', 'MR', 'MX', 'NAPTR', 'NIMLOC',
    'NS', 'NSAP', 'NSAP_PTR', 'NSEC', 'NSEC3', 'NSEC3PARAMS', 'NULL', 'NXT', 'OPT', 'PTR', 'PX', 'RP', 'RRSIG', 'RT', 'SIG', 'SINK', 'SOA', 'SRV', 'SSHFP',
    'TSIG', 'TXT', 'UID', 'UINFO', 'UNSPEC', 'WKS', 'X25');

  $dns_class = array('ANY', 'CH', 'HS', 'IN', 'NONE');

  $dns_rcode = array('FORMERR', 'NOERROR', 'NOTAUTH' ,'NOTIMPL', 'NOTZONE', 'NXDOMAIN', 'NXRRSET', 'REFUSED', 'SERVFAIL', 'YXDOMAIN', 'YXRRSET', 'nodata');

  $dns_opcode = array('QUERY', 'IQUERY', 'STATUS', 'NOTIFY', 'UPDATE');

  $dns_flags = array('QR', 'AA', 'TC', 'RD', 'RA', 'Z', 'AD', 'CD');

  $rrd_filename = "app-unbound-$app_id-queries.rrd";
  foreach ($dns_qtype as $qtype)
  {
    $rrd_queries .= "DS:qType$qtype:DERIVE:600:0:125000000000 ";
  }

  foreach ($dns_class as $class)
  {
    $rrd_queries .= "DS:class$class:DERIVE:600:0:125000000000 ";
  }

  foreach ($dns_rcode as $rcode)
  {
    $rrd_queries .= "DS:rcode$rcode:COUNTER:600:0:125000000000 ";
  }

  foreach ($dns_flags as $flag)
  {
    $rrd_queries .= "DS:flag$flag:COUNTER:600:0:125000000000 ";
  }

  foreach ($dns_opcode as $opcode)
  {
    $rrd_queries .= "DS:opcode$opcode:DERIVE:600:0:125000000000 ";
  }

  $rrd_queries .= "DS:numQueryTCP:DERIVE:600:0:125000000000 \
    DS:numQueryIPv6:DERIVE:600:0:125000000000 \
    DS:numQueryUnwanted:DERIVE:600:0:125000000000 \
    DS:numReplyUnwanted:DERIVE:600:0:125000000000 \
    DS:numAnswerSecure:DERIVE:600:0:125000000000 \
    DS:numAnswerBogus:DERIVE:600:0:125000000000 \
    DS:numRRSetBogus:DERIVE:600:0:125000000000 \
    DS:ednsPresent:DERIVE:600:0:125000000000 \
    DS:ednsDO:DERIVE:600:0:125000000000 \
    ";

  rrdtool_create($device, $rrd_filename, "" . $rrd_queries );

  # We return 0 in the following loops because unbound does not show these values if they are 0.
  # They're not unknown (U) in this case, so it's ok to return 0.

  foreach ($dns_qtype as $qtype)
  {
    $rrd_values[] = (is_numeric($unbound["num.query.type.$qtype"]) ? $unbound["num.query.type.$qtype"] : "0");
  }

  foreach ($dns_class as $class)
  {
    $rrd_values[] = (is_numeric($unbound["num.query.class.$class"]) ? $unbound["num.query.class.$class"] : "0");
  }

  foreach ($dns_rcode as $rcode)
  {
    $rrd_values[] = (is_numeric($unbound["num.answer.rcode.$rcode"]) ? $unbound["num.answer.rcode.$rcode"] : "0");
  }

  foreach ($dns_opcode as $opcode)
  {
    $rrd_values[] = (is_numeric($unbound["num.query.opcode.$opcode"]) ? $unbound["num.query.opcode.$opcode"] : "0");
  }

  foreach ($dns_flags as $flag)
  {
    $rrd_values[] = (is_numeric($unbound["num.query.flags.$flag"]) ? $unbound["num.query.flags.$flag"] : "0");
  }

  foreach (array('num.query.tcp', 'num.query.ipv6', 'unwanted.queries', 'unwanted.replies', 'num.answer.secure', 'num.answer.bogus', 'num.rrset.bogus',
    'num.query.edns.present', 'num.query.edns.DO') as $key)
  {
    $rrd_values[] = (is_numeric($unbound[$key]) ? $unbound[$key] : "U");
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));
  unset($rrd_values);
}

// EOF
