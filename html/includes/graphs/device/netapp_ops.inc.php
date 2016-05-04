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

$rrd_filename = get_rrd_path($device, 'netapp_stats.rrd');
$rrd_exists   = is_file($rrd_filename);

$count = 0;
foreach (array('iscsi', 'nfs', 'cifs', 'http','fcp') as $stat)
{
  if (!$rrd_exists) {
    continue;
  }

  $rrd_list[$count]['filename'] = $rrd_filename;
  $rrd_list[$count]['descr']    = nicecase($stat);
  $rrd_list[$count]['ds']       = $stat .'_ops';

  $count++;
}

$unit_text   = 'Operations/s';
$colours     = 'mixed';
$units       = '';
$total_units = '';

$scale_min = '0';
$scale_max = '100';
$divider   = $count;
$text_orig = 1;
$nototal   = 1;

include('includes/graphs/generic_multi_simplex_separated.inc.php');

// EOF
