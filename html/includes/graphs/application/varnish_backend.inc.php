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

include_once($config['html_dir'].'/includes/graphs/common.inc.php');

$rrd_filename = get_rrd_path($device, 'app-varnish-'.$app['app_id'].'.rrd');

$array = array(
	'backend_req' => array('descr' => 'Requests', 'colour' => '274D15'),
	'backend_reuse' => array('descr' => 'Reuses', 'colour' => '468727'),
	'backend_recycle' => array('descr' => 'Recycles', 'colour' => '658A54'),
	'backend_toolate' => array('descr' => 'Closed', 'colour' => 'FFBCC0FF'),
	'backend_unhealthy' => array('descr' => 'Unhealthy', 'colour' => '750F7DFF'),
	'backend_busy' => array('descr' => 'Busy', 'colour' => '4444FFFF'),
	'backend_retry' => array('descr' => 'Retries', 'colour' => 'FFA000FF'),
	'backend_fail' => array('descr' => 'Failures', 'colour' => 'FF0000FF'),
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

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Conn.";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
