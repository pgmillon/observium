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

$rrd_filename = get_rrd_path($device, 'app-mysql-'.$app["app_id"].'.rrd');

$array = array('MaCs'  => array('descr' => 'Max Connections', 'colour' => '22FF22'),
               'MUCs'  => array('descr' => 'Max Used Connections', 'colour' => '0022FF'),
               'ACs'   => array('descr' => 'Aborted Clients', 'colour' => 'FF0000'),
               'AdCs'  => array('descr' => 'Aborted Connects', 'colour' => '0080C0'),
               'TCd'   => array('descr' => 'Threads Connected', 'colour' => 'FF0000'),
               'Cs'    => array('descr' => 'New Connections', 'colour' => '0080C0'),
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
$unit_text = "Connections";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
