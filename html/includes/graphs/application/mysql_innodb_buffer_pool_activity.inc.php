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

$array = array('IBRd'  => 'Pages Read',
               'IBCd'  => 'Pages Created',
               'IBWr'  => 'Pages Written',
);

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    if (is_array($data))
    {
      $rrd_list[$i]['descr'] = $data['descr'];
    } else {
      $rrd_list[$i]['descr'] = $data;
    }
    $rrd_list[$i]['ds'] = $ds;
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Activity";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
