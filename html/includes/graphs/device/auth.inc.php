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

if ($auth || device_permitted($device['device_id']))
{
  $title = generate_device_link($device);
  $graph_title = $device['hostname'];
  $auth = TRUE; ///FIXME. Who? --mike
}

//EOL
