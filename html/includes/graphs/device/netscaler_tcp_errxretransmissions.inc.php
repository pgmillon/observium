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

$i = 0;

$rrd_filename = get_rrd_path($device, "netscaler-stats-tcp.rrd");

$xre = array('ErrFirstRetransmiss' => 'First',
             'ErrSecondRetransmis' => 'Second',
             'ErrThirdRetransmiss' => 'Third',
             'ErrForthRetransmiss' => 'Fourth',
             'ErrFifthRetransmiss' => 'Fifth',
             'ErrSixthRetransmiss' => 'Sixth',
             'ErrSeventhRetransmi' => 'Seventh');

foreach ($xre as $stat => $descr)
{
  if (is_file($rrd_filename))
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = $stat;
    $i++;
  }
}

$unit_text = "Retransmissions";

$units = '';
$total_units = '';
$colours = 'reds_8';

$scale_min = "0";
#$scale_max = "100";

$divider = $i;
$text_orig = 1;
$nototal = 1;

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
