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

$mysql_rrd = get_rrd_path($device, "app-mysql-".$app['app_id'].".rrd");

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$array = array('QCQICe'  => array('descr' => 'Queries in cache', 'colour' => '22FF22'),
               'QCHs'  => array('descr' => 'Cache hits', 'colour' => '0022FF'),
               'QCIs' => array('descr' => 'Inserts', 'colour' => 'FF0000'),
               'QCNCd'  => array('descr' => 'Not cached', 'colour' => '00AAAA'),
               'QCLMPs'  => array('descr' => 'Low-memory prunes', 'colour' => 'FF00FF'),
);

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
#    $rrd_list[$i]['colour'] = $data['colour'];
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Commands";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
