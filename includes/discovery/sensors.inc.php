<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$valid['sensor'] = array();

echo("Sensors: ");

$include_dir = "includes/discovery/sensors";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

// Always include ENTITY-SENSOR-MIB
// Do this after the above include, as it checks for duplicates from CISCO-ENTITY-SENSOR-MIB
include($config['install_dir']."/includes/discovery/sensors/entity-sensor-mib.inc.php");

if ($debug && count($valid['sensor'])) { print_vars($valid['sensor']); }
foreach (array_keys($config['sensor_types']) as $type)
{
  check_valid_sensors($device, $type, $valid['sensor']);
}

echo(PHP_EOL);

// EOF
