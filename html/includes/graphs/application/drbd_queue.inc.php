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

$rrd_filename = get_rrd_path($device, "app-drbd-".$app['app_instance'].".rrd");

$array = array(
                'lo' => 'Local I/O',
                'pe' => 'Pending',
                'ua' => 'UnAcked',
                'ap' => 'App Pending',
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
$nototal   = 0;
$unit_text = "";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
