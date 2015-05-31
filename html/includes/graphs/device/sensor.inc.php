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

//include_once($config['html_dir']."/includes/graphs/common.inc.php");

foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_index`", array($class, $device['device_id'])) as $sensor)
{
  $rrd_filename = get_rrd_path($device, get_sensor_rrd($device, $sensor));

  if (is_file($rrd_filename))
  {
    $descr = rewrite_hrDevice($sensor['sensor_descr']);
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $descr;
    $rrd_list[$i]['ds'] = "sensor";
    $i++;
  }
}

$unit_text = $unit_long;

$units = '%';
$total_units = '%';
$colours ='mixed';
$nototal = 1;
$scale_rigid = FALSE;

include("includes/graphs/generic_multi_line.inc.php");

// EOF
