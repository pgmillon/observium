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

if (is_numeric($vars['id']))
{
  $storage = dbFetchRow("SELECT * FROM `storage` WHERE `storage_id` = ?", array($vars['id']));

  if (is_numeric($storage['device_id']) && ($auth || device_permitted($storage['device_id'])))
  {
    $device = device_by_id_cache($storage['device_id']);
    $rrd_filename = get_rrd_path($device, "storage-" . $storage['storage_mib'] . "-" . $storage['storage_descr'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Storage :: " . htmlentities($storage['storage_descr']);
    $auth = TRUE;
  }
}

?>
