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
  $entity = get_entity_by_id_cache($type, $vars['id']);

  if (is_numeric($entity['device_id']) && ($auth || device_permitted($entity['device_id'])))
  {
    $device = device_by_id_cache($entity['device_id']);
    $title  = generate_device_link($device);
    $title .= " :: Pseudowire :: " . escape_html($entity['pwID']);
    $auth = TRUE;

    $index        = strtolower($entity['mib']) . '-' . $entity['pwIndex'];
    if ($subtype == 'uptime')
    {
      $index      = strtolower($entity['mib']) . '-uptime-' . $entity['pwIndex'];
    }
    $unit_text    = 'PW '.$entity['pwID'];
    if ($entity['pwDescr'])
    {
      $unit_text .= ' - '.$entity['pwDescr'];
    }

    $graph_title  = $device['hostname'] . ' :: ' . $unit_text; // hostname :: SLA XX

    $rrd_filename = get_rrd_path($device, "pseudowire-" . $index . ".rrd");
    $graph_return['rrds'][] = $rrd_filename;
    $auth = TRUE;
  }
}

