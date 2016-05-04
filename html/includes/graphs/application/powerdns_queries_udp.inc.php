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
$unit_text    = "Queries/sec";
$rrd_filename = get_rrd_path($device, "app-powerdns-".$app['app_id'].".rrd");
$array        = array(
                      'q_udp4Queries' => array('descr' => 'UDP4 Queries', 'colour' => '000088FF', 'invert' => 0),
                      'q_udp6Queries' => array('descr' => 'UDP6 Queries', 'colour' => '880000FF', 'invert' => 0),
                      'q_udp4Answers' => array('descr' => 'UDP4 Answers', 'colour' => '00008888', 'invert' => 1),
                      'q_udp6Answers' => array('descr' => 'UDP6 Answers', 'colour' => '88000088', 'invert' => 1),
                     );

$i            = 0;

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

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
