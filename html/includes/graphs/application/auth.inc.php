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

if (is_numeric($vars['id']) && ($auth || application_permitted($vars['id'])))
{
  $app    = get_application_by_id($vars['id']);
  $device = device_by_id_cache($app['device_id']);
  $title  = generate_device_link($device);
  $title .= $graph_subtype;
  $auth   = TRUE;
}

// EOF
