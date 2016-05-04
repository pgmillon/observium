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
Not showing stats for:
- Different servers
- Timings
i.e.
serverX.queries
time.boot
time.elapsed

server0.queries=0
num.queries=0
time.boot=85706.132516
time.elapsed=120.662870
size.db.disk=67072
size.db.mem=38008
size.xfrd.mem=20990792
size.config.disk=0
size.config.mem=5184
num.type.A=0
num.type.NS=0
num.type.MD=0
num.type.MF=0
num.type.CNAME=0
num.type.SOA=0
num.type.MB=0
num.type.MG=0
num.type.MR=0
num.type.NULL=0
num.type.WKS=0
num.type.PTR=0
num.type.HINFO=0
num.type.MINFO=0
num.type.MX=0
num.type.TXT=0
num.type.RP=0
num.type.AFSDB=0
num.type.X25=0
num.type.ISDN=0
num.type.RT=0
num.type.NSAP=0
num.type.SIG=0
num.type.KEY=0
num.type.PX=0
num.type.AAAA=0
num.type.LOC=0
num.type.NXT=0
num.type.SRV=0
num.type.NAPTR=0
num.type.KX=0
num.type.CERT=0
num.type.DNAME=0
num.type.OPT=0
num.type.APL=0
num.type.DS=0
num.type.SSHFP=0
num.type.IPSECKEY=0
num.type.RRSIG=0
num.type.NSEC=0
num.type.DNSKEY=0
num.type.DHCID=0
num.type.NSEC3=0
num.type.NSEC3PARAM=0
num.type.TLSA=0
num.type.SPF=0
num.type.NID=0
num.type.L32=0
num.type.L64=0
num.type.LP=0
num.type.EUI48=0
num.type.EUI64=0
num.opcode.QUERY=0
num.class.IN=0
num.rcode.NOERROR=0
num.rcode.FORMERR=0
num.rcode.SERVFAIL=0
num.rcode.NXDOMAIN=0
num.rcode.NOTIMP=0
num.rcode.REFUSED=0
num.rcode.YXDOMAIN=0
num.edns=0
num.ednserr=0
num.udp=0
num.udp6=0
num.tcp=0
num.tcp6=0
num.answer_wo_aa=0
num.rxerr=0
num.txerr=0
num.raxfr=0
num.truncated=0
num.dropped=0
zone.master=3
zone.slave=0
*/

if (!empty($agent_data['app']['nsd']))
{
  discover_app($device, 'nsd');
  
  foreach (explode("\n",$agent_data['app']['nsd']) as $line)
  {
    list($key,$value) = explode("=",$line,2);
    $nsd[$key] = $value;
  }

  // Memory
  $rrd_filename = "app-nsd-memory.rrd";

  rrdtool_create($device, $rrd_filename, " \
    DS:memDBDisk:GAUGE:600:0:125000000000 \
    DS:memDBMem:GAUGE:600:0:125000000000 \
    DS:memXFRDMem:GAUGE:600:0:125000000000 \
    DS:memConfDisk:GAUGE:600:0:125000000000 \
    DS:memConfMem:GAUGE:600:0:125000000000 ");

  foreach(array("size.db.disk","size.db.mem","size.xfrd.mem","size.config.disk","size.config.disk") as $key)
  {
    $rrd_values[] = $nsd[$key];
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));

  unset($rrd_values);

  // Zones
  $rrd_filename = "app-nsd-zones.rrd";

  rrdtool_create($device, $rrd_filename, " \
    DS:zoneMaster:GAUGE:600:0:125000000000 \
    DS:zoneSlave:GAUGE:600:0:125000000000 ");

  foreach(array("zone.master","zone.slave") as $key)
  {
    $rrd_values[] = $nsd[$key];
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));

  unset($rrd_values);

  // Queries
  $rrd_filename = "app-nsd-queries.rrd";

  $dns_qtype = array(
    'A','NS','MD','MF','CNAME','SOA','MB','MG','MR','NULL','WKS','PTR','HINFO','MINFO','MX','TXT','RP','AFSDB','X25',
    'ISDN','RT','NSAP','SIG','KEY','PX','AAAA','LOC','NXT','SRV','NAPTR','KX','CERT','DNAME','OPT','APL','DS','SSHFP','IPSECKEY',
    'RRSIG','NSEC','DNSKEY','DHCID','NSEC3','NSEC3PARAM','TLSA','SPF','NID','L32','L64','LP','EUI48','EUI64');

  $dns_rcode = array('FORMERR', 'NOERROR', 'NOTIMP', 'NXDOMAIN', 'REFUSED', 'SERVFAIL', 'YXDOMAIN');

  foreach ($dns_qtype as $qtype)
  {
    $rrd_queries .= "DS:qType$qtype:DERIVE:600:0:125000000000 ";
  }

  $rrd_queries .= "DS:classIN:DERIVE:600:0:125000000000 ";

  foreach ($dns_rcode as $rcode)
  {
    $rrd_queries .= "DS:rcode$rcode:DERIVE:600:0:125000000000 ";
  }

  $rrd_queries .= "DS:opcodeIN:DERIVE:600:0:125000000000 ";

  $rrd_queries .= "DS:numQueries:DERIVE:600:0:125000000000 ";

  $rrd_queries .= 
    "DS:numQueryUDP:DERIVE:600:0:125000000000 \
    DS:numQueryUDP6:DERIVE:600:0:125000000000 \
    DS:numQueryTCP:DERIVE:600:0:125000000000 \
    DS:numQueryTCP6:DERIVE:600:0:125000000000 \
    DS:numQueryEDNS:DERIVE:600:0:125000000000 \
    DS:numQueryEDNSErr:DERIVE:600:0:125000000000 \
    DS:numQueryRecieveErr:DERIVE:600:0:125000000000 \
    DS:numQueryTransferErr:DERIVE:600:0:125000000000 \
    DS:numRequestAXFR:DERIVE:600:0:125000000000 \
    DS:numQueryTruncated:DERIVE:600:0:125000000000 \
    DS:numQueryDropped:DERIVE:600:0:125000000000 \
    DS:numQueriesWoAA:DERIVE:600:0:125000000000 \
    ";

  rrdtool_create($device, $rrd_filename, "" . $rrd_queries );

  foreach ($dns_qtype as $qtype)
  {
    $rrd_values[] = $nsd["num.type.$qtype"];
  }

  $rrd_values[] = $nsd["num.class.IN"];

  foreach ($dns_rcode as $rcode)
  {
    $rrd_values[] = $nsd["num.rcode.$rcode"];
  }

  $rrd_values[] = $nsd["num.opcode.IN"];
  $rrd_values[] = $nsd["num.queries"];
  

  foreach (array('num.udp','num.udp6', 'num.tcp','num.tcp6', 'num.edns','num.ednserr','num.rxerr','num.txerr','num.raxfr','num.truncated','num.dropped','num.answer_wo_aa') as $key)
  {
    $rrd_values[] = $nsd[$key];
  }

  rrdtool_update($device, $rrd_filename, "N:" . implode(':', $rrd_values));

  unset($rrd_values);

  $serverNum = 0;
  while (1)
  {    
    if (isset($nsd["server$serverNum.queries"]))
    {
      $rrd_filename = "app-nsd-server$serverNum.rrd";
      
      rrdtool_create($device, $rrd_filename, "DS:numQueries:DERIVE:600:0:125000000000");

      rrdtool_update($device, $rrd_filename, "N:" . $nsd["server$serverNum.queries"]);
      $serverNum++;
    }
    else
    {
      break;
    }
  }

  unset($serverNum);
}

// EOF
