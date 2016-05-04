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

if (is_numeric($vars['device']) && ($auth || device_permitted($vars['device'])))
{
  $device = device_by_id_cache($vars['device']);
  $title = generate_device_link($device);
  $graph_title = $device['hostname'];
  $auth = TRUE;
}

// EOF
