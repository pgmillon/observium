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

$rrd_filename = get_rrd_path($device, "app-nfsd-".$app['app_id'].".rrd");

$array = array(
  "n_count", "u_count", "t_data", "t_conn"
);

$i = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $name)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $name;
    $rrd_list[$i]['ds'] = 'net'.$name;
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 0;
$unit_text = "Rows";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
