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

$scale_min    = 0;
$colours      = "mixed";
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Entries";
$rrd_filename = get_rrd_path($device, "app-powerdns-recursor-".$app['app_id'].".rrd");
$array        = array(
                      'cacheEntries'       => array('descr' => 'Cache entries', 'colour' => 'FF0000FF'),
                      'packetCacheEntries' => array('descr' => 'Packet cache entries', 'colour' => 'FFFF00FF'),
                      'negcacheEntries'    => array('descr' => 'Negative cache entries', 'colour' => '0000FFFF'),
                      'nsSpeedsEntries'    => array('descr' => 'NS speeds entries', 'colour' => '00FF00FF'),
                      'throttleEntries'    => array('descr' => 'Throttle map entries', 'colour' => '00FFF0FF'),
                     );

$i            = 0;

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

include("includes/graphs/generic_multi_line.inc.php");

// EOF
