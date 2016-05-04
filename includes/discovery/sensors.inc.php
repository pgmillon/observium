<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$valid['sensor'] = array();
$valid['status'] = array();

// Sensors and Status are discovered together since they are often in the same MIBs trying to split them would likely just cause a lot of code duplication.
//

echo("Sensors & Status: ");

// Run sensor discovery scripts (also discovers state sensors as status entities)
$include_dir = "includes/discovery/sensors";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

// Run status-specific discovery scripts
$include_dir = "includes/discovery/status";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

// Detect sensors by simple MIB-based discovery :
// FIXME - this should also be extended to understand multiple entries in a table, and take descr from an OID but this is all I need right now :)
foreach($config['os'][$device['os']]['mibs'] AS $mib)
{
  if(is_array($config['mibs'][$mib]['sensor']))
  {
    echo(' '.$mib.': ');
    foreach($config['mibs'][$mib]['sensor'] AS $entry_name => $entry)
    {
      echo($entry_name.' ');
      $usage = snmp_get($device, $entry['oid'], '-OQUvs', $mib, mib_dirs($config['mibs'][$mib]['mib_dir']));
      if (is_numeric($usage))
      {
        // discover_sensor(&$valid, $class, $device, $oid, $index, $type, $sensor_descr, $scale = 1, $current = NULL, $options = array(), $poller_type = 'snmp')

        discover_sensor($valid['sensor'], $entry['class'], $device, $entry['oid_num'], 0, $entry_name, $entry['descr'], 1, $usage);
      }
    }
  }
}


if (OBS_DEBUG > 1 && count($valid['sensor'])) { print_vars($valid['sensor']); }
foreach (array_keys($config['sensor_types']) as $type)
{
  check_valid_sensors($device, $type, $valid['sensor']);
}

if (OBS_DEBUG > 1 && count($valid['status'])) { print_vars($valid['status']); }
check_valid_status($device, $GLOBALS['valid']['status']);

echo(PHP_EOL);

// EOF
