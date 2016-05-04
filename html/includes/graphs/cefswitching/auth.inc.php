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

if (is_numeric($vars['id']))
{
  $cef = dbFetchRow("SELECT * FROM `cef_switching` AS C, `devices` AS D WHERE C.cef_switching_id = ? AND C.device_id = D.device_id", array($vars['id']));

  if (is_numeric($cef['device_id']) && ($auth || device_permitted($cef['device_id'])))
  {
    $device = device_by_id_cache($cef['device_id']);

    $rrd_filename = get_rrd_path($device, "cefswitching-".$cef['entPhysicalIndex']."-".$cef['afi']."-".$cef['cef_index'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: CEF Switching :: " . escape_html($cef['cef_descr']);
    $auth = TRUE;
  }
}

// EOF
