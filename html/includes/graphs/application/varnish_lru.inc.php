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
	'lru_moved' => array('descr' => 'Moved', 'colour' => '4444FFFF'),
	'lru_nuked' => array('descr' => 'Nuked', 'colour' => 'FF0000FF'),
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
$unit_text = "Req.";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
