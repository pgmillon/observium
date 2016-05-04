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

$i = 1;

foreach (dbFetchRows("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE D.device_id = ? AND U.device_id = D.device_id", array($device['device_id'])) as $disk)
{
  $rrd_filename = get_rrd_path($device, "ucd_diskio-" . $disk['diskio_descr'] . ".rrd");
  if (is_file($rrd_filename))
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $disk['diskio_descr'];
    $rrd_list[$i]['ds_in'] = $ds_in;
    $rrd_list[$i]['ds_out'] = $ds_out;
    $i++;
  }
}

?>
