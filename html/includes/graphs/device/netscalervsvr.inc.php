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

// Generate a list of vsvrs and build an rrd_list array using arguments passed from parent

foreach (dbFetchRows("SELECT * FROM `netscaler_vservers` WHERE `device_id` = ?", array($device['device_id'])) as $vsvr)
{
  $rrd_filename = get_rrd_path($device, "netscaler-vsvr-".$vsvr['vsvr_name'].".rrd");

  if (is_file($rrd_filename))
  {
    $rrd_list[$i]['filename']  = $rrd_filename;
    $rrd_list[$i]['descr']     = $vsvr['vsvr_name'];
    $rrd_list[$i]['descr_in']  = $vsvr['vsvr_name'];
    $rrd_list[$i]['descr_out'] = $vsvr['vsvr_ip'] . ":" . $vsvr['vsvr_port'];
    $rrd_list[$i]['ds_in']     = $ds_in;
    $rrd_list[$i]['ds_out']    = $ds_out;
    $i++;
  }

  unset($ignore);
}

// EOF
