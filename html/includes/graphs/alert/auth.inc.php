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

if (is_numeric($vars['id']) && $alert = get_alert_entry_by_id($vars['id']))
{

  $entity = get_entity_by_id_cache($alert['entity_type'], $alert['entity_id']);
  $device = device_by_id_cache($alert['device_id']);

  if (device_permitted($device['device_id']) || $auth)
  {

    $title  = generate_device_link($device);

    $title_array   = array();
    $title_array[] = array('text' => $device['hostname'], 'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'])));

    $auth   = TRUE;

    $rrd_filename = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("alert-".$alert['alert_table_id'].".rrd");

  }
} else {
  // error?
}

?>
