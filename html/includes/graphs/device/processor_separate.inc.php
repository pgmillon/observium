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

foreach ($procs as $proc)
{
  $rrd_filename = get_rrd_path($device, "processor-" . $proc['processor_type'] . "-" . $proc['processor_index'] . ".rrd");

  if (is_file($rrd_filename))
  {
    $descr = rewrite_hrDevice($proc['processor_descr']);

    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = "usage";
    $rrd_list[$i]['area'] = 1;
    $i++;
  }
}

$unit_text = 'Usage';

$units = '%';
$total_units = '%';
$colours ='mixed-10c';

$scale_min = "0";
$scale_max = "100";

$nototal = 1;

include("includes/graphs/generic_multi_line.inc.php");

// EOF
