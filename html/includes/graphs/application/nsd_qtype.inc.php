<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$colours      = "mixed";
$nototal      = 1;
$unit_text    = "Queries/sec";
$rrd_filename = get_rrd_path($device,"app-nsd-queries.rrd");

$array        = array();

# Simplified list
$dns_qtype = array(
	'A','AAAA','AFSDB','APL','CERT','CNAME','DHCID','DNAME','DNSKEY','DS',
	'EUI48','EUI64','HINFO','IPSECKEY','ISDN','KEY','KX','L32','L64','LOC',
	'LP','MB','MD','MF','MG','MINFO','MR','MX','NAPTR','NID','NS','NSAP',
	'NSEC','NSEC3','NSEC3PARAM','NULL','NXT','OPT','PTR','PX','RP','RRSIG',
	'RT','SIG','SOA','SPF','SRV','SSHFP','TLSA','TXT','WKS','X25');

$colours = $config['graph_colours']['mixed']; # needs moar colours!

foreach ($dns_qtype as $qtype)
{
  $array["qType$qtype"] = array('descr' => "$qtype", 'colour' => $colours[(count($array) % count($colours))]);
}

$i = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = $data['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
