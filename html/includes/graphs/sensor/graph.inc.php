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

/// FIXME. To unify all sensor graphs.
switch ($sensor['sensor_class'])
{
  case 'humidity':
  case 'capacity':
  case 'load':
    include("percent.inc.php");
    break;
  default:
    if (is_file($sensor['sensor_class'].".inc.php"))
    {
      include($sensor['sensor_class'].".inc.php");
    } else {
      graph_error($type.'_'.$subtype); // Graph Template Missing;
    }
}

// EOF
