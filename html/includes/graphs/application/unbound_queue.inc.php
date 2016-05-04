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
$nototal      = 1;
$unit_text    = "Queries";
$rrd_filename = get_rrd_path($device, "app-unbound-".$app['app_id']."-total.rrd");

$array        = array(
                      'reqListAvg' => array('descr' => 'Average size', 'colour' => '00FF00FF'),
                      'reqListMax' => array('descr' => 'Max size', 'colour' => '0000FFFF'),
                      'reqListOverwritten' => array('descr' => 'Replaced', 'colour' => 'FF0000FF'),
                      'reqListExceeded' => array('descr' => 'Dropped', 'colour' => '00FFFFFF'),
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
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
