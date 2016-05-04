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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$colours      = "mixed";
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Entries";
$rrd_filename = get_rrd_path($device, "app-bind-".$app['app_id']."-cache-default.rrd");

#$rrtypes = array('SOA', 'A', 'AAAA', 'NS', 'MX', 'CNAME', 'TXT', 'PTR', 'DNSKEY', 'RRSIG');
$rrtypes = array('SOA', 'A', 'AAAA', 'NS', 'MX', 'CNAME', 'DNAME', 'TXT', 'SPF', 'SRV', 'SSHFP', 'TLSA', 'IPSECKEY', 'PTR', 'DNSKEY', 'RRSIG', 'NSEC', 'NSEC3', 'NSEC3PARAM', 'DS', 'DLV');
$array = array();
foreach ($rrtypes as $rrtype)
{
  // Consistent random colours, offset picked for funny colours :-)
  $colour = substr(md5($rrtype), 1, 6);
  $array[$rrtype] = array('descr' => $rrtype, 'colour' => $colour, 'invert' => False);
  $array['NEG_'.$rrtype] = array('descr' => '!'.$rrtype, 'colour' => $colour, 'invert' => True);
}
$i = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr']    = $data['descr'];
    $rrd_list[$i]['ds']       = $ds;
    $rrd_list[$i]['colour']   = $data['colour'];
    $rrd_list[$i]['invert']   = $data['invert'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

#include("includes/graphs/generic_multi_line.inc.php");
include("includes/graphs/generic_multi.inc.php");

// EOF
