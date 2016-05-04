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
$unit_text    = "Count";
$rrd_filename = get_rrd_path($device, "app-bind-".$app['app_id']."-zone-maint.rrd");

$array = array(
  'NotifyOutv4' => array('descr' => "Notifies sent IPv4", 'colour' => '87cefa'),
  'NotifyOutv6' => array('descr' => "Notifies sent IPv6", 'colour' => '00bfff'),
  'NotifyInv4' => array('descr' => "Notifies received IPv4", 'colour' => '3cb371'),
  'NotifyInv6' => array('descr' => "Notifies received IPv6", 'colour' => '2e8b57'),
  'NotifyRej' => array('descr' => "Notifies rejected", 'colour' => 'ff8c00'),
  'SOAOutv4' => array('descr' => "SOA queries sent IPv4", 'colour' => 'daa520'),
  'SOAOutv6' => array('descr' => "SOA queries sent IPv6", 'colour' => 'b8860b'),
  'AXFRReqv4' => array('descr' => "AXFR requested IPv4", 'colour' => 'da70d6'),
  'AXFRReqv6' => array('descr' => "AXFR requested IPv6", 'colour' => '9932cc'),
  'IXFRReqv4' => array('descr' => "IXFR requested IPv4", 'colour' => 'ff69b4'),
  'IXFRReqv6' => array('descr' => "IXFR requested IPv6", 'colour' => 'ff1493'),
  'XfrSuccess' => array('descr' => "Successful transfer", 'colour' => '32cd32'),
  'XfrFail' => array('descr' => "Failed transfer", 'colour' => 'ff0000'),
);
$i = 0;

if (is_file($rrd_filename))
{
    foreach ($array as $ds => $data)
    {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $data['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $data['colour'];
        $i++;
    }
} else {
    echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
