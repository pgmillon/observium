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


$units = '%';
$unit_text = 'Utilisation';
$total_units = '';

$i = 1;

$rrd_list = array();

foreach ($vars['id'] as $port_id)
{

  $processor = dbFetchRow("SELECT * FROM `processors` WHERE `processor_id` = ?", array($port_id));

  $device = device_by_id_cache($processor['device_id']);

  $rrd_filename  = get_rrd_path($device, "processor-" . $processor['processor_type'] . "-" . $processor['processor_index'] . ".rrd");

  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = $processor['processor_descr'];
  $rrd_list[$i]['ds'] = 'usage';

  $i++;
}

$colours='mixed';

$scale_min = "0";
$nototal = 1;
$simple_rrd = TRUE;

include("includes/graphs/generic_multi_line.inc.php");

// EOF

