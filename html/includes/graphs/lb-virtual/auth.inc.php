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
  $virt = dbFetchRow("SELECT * FROM `lb_virtuals` AS I, `devices` AS D WHERE I.virt_id = ? AND I.device_id = D.device_id", array($vars['id']));

  if (is_numeric($virt['device_id']) && ($auth || device_permitted($virt['device_id'])))
  {
    $device = device_by_id_cache($virt['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/lb-virtual-".safename($virt['virt_name']).".rrd";

    $title  = generate_device_link($device);
    $title .= " :: LB Virtual :: " . htmlentities($virt['virt_name']);
    $auth = TRUE;
  }
}

// EOF
