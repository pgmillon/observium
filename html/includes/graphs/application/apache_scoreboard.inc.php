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

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-apache-".$app['app_id'].".rrd");

$array = array('sb_reading' => array('descr' => 'Reading'),
               'sb_writing' => array('descr' => 'Writing'),
               'sb_wait' => array('descr' => 'Waiting'),
               'sb_start' => array('descr' => 'Starting'),
               'sb_keepalive' => array('descr' => 'Keepalive'),
               'sb_dns' => array('descr' => 'DNS'),
               'sb_closing' => array('descr' => 'Closing'),
               'sb_logging' => array('descr' => 'Logging'),
               'sb_graceful' => array('descr' => 'Graceful'),
               'sb_idle' => array('descr' => 'Idle'),
);

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
} else { echo("file missing: $file");  }

$colours   = "mixed-10c";
$nototal   = 1;
$unit_text = "Workers";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
