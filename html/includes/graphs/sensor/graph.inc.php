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

/// FIXME. To unify all sensor graphs.
switch ($sensor['sensor_class'])
{
  case 'humidity':
  case 'capacity':
  case 'load':
    include("percent.inc.php");
    break;
  default:
    $include = $config['html_dir'] . "/includes/graphs/$type/".$sensor['sensor_class'].".inc.php";
    if (is_file($include))
    {
      include($include);
    } else {
      graph_error($type.'_'.$subtype); // Graph Template Missing;
    }
}

// EOF
