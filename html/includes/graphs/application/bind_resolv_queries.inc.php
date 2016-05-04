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
$rrd_filename = get_rrd_path($device, "app-bind-".$app['app_id']."-resolver-default.rrd");

$array = array(
  'Queryv4' => array('descr' => "Queries sent IPv4", 'colour' => '87cefa'),
  'Responsev4' => array('descr' => "Responses received IPv4", 'colour' => '00bfff'),
  'Queryv6' => array('descr' => "Queries sent IPv6", 'colour' => 'ff69b4'),
  'Responsev6' => array('descr' => "Responses received IPv6", 'colour' => 'ff1493'),
  'NXDOMAIN' => array('descr' => "NXDOMAIN received", 'colour' => 'ffa07a', 'invert' => True),
  'SERVFAIL' => array('descr' => "SERVFAIL received", 'colour' => 'ff6533', 'invert' => True),
  'FORMERR' => array('descr' => "FORMERR received", 'colour' => 'ff8c00', 'invert' => True),
  'OtherError' => array('descr' => "Other error received", 'colour' => 'ff0000', 'invert' => True),
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
        $rrd_list[$i]['invert']   = $data['invert'];
        $i++;
    }
} else {
    echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
