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

$rrdfile = get_rrd_path($device, "wifi-radio-". $radio['serial'] . '-' . $radio['radio_number'].".rrd");

$rrd_list[0]['filename'] = $rrdfile;
$rrd_list[0]['descr'] = "Retries";
$rrd_list[0]['ds'] = "TxRetriesCount";

$unit_text = "Retries";

$units='';
$total_units='';
$colours='mixed';

$scale_min = "0";

$nototal = 1;

if ($rrd_list)
{
  include("includes/graphs/generic_multi_line.inc.php");
}

?>
