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
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Packets";
$rrd_filename = get_rrd_path($device, "app-ntpd-server-".$app['app_id'].".rrd");
$array        = array(
                      'packets_drop' => array('descr' => 'Dropped', 'colour' => '880000FF'),
                      'packets_ignore' => array('descr' => 'Ignored', 'colour' => 'FF8800FF')
                     );

if (is_file($rrd_filename))
{
  $i = 0;

  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename']  = $rrd_filename;
    $rrd_list[$i]['descr']     = $data['descr'];
    $rrd_list[$i]['ds']        = $ds;
    $rrd_list[$i]['colour']    = $data['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
