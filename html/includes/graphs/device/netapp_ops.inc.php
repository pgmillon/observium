<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$i = 0;

foreach (array('iscsi', 'nfs', 'cifs', 'http','fcp') as $stat)
{
  $rrd_filename = get_rrd_path($device, "netapp_stats.rrd");

  if (is_file($rrd_filename))
  {
    $descr = nicecase($stat);

    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $stat.'_ops';
    $i++;
  }
}

$unit_text = "Operations";

$units = '';
$total_units = '';
$colours = 'mixed';

$scale_min = "0";
$scale_max = "100";

$divider = $i;
$text_orig = 1;
$nototal = 1;

include("includes/graphs/generic_multi_simplex_separated.inc.php");

?>
