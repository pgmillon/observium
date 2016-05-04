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

if ($auth || device_permitted($device['device_id']))
{
  $title = generate_device_link($device);
  $graph_title = $device['hostname'];
  $auth = TRUE; ///FIXME. Who? --mike
}

//EOL
