<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$valid['sensor'] = array();
$valid['status'] = array();

// Sensors and Status are discovered together since they are often in the same MIBs trying to split them would likely just cause a lot of code duplication.
//

// Run sensor discovery scripts (also discovers state sensors as status entities)
$include_dir = "includes/discovery/sensors";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

// Run status-specific discovery scripts
$include_dir = "includes/discovery/status";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

// Detect sensors by simple MIB-based discovery :
// FIXME - this should also be extended to understand multiple entries in a table :)
foreach (get_device_mibs($device) as $mib)
{
  if (is_array($config['mibs'][$mib]['sensor']))
  {
    print_cli_data_field($mib);
    foreach ($config['mibs'][$mib]['sensor'] as $oid => $oid_data)
    {
      print_cli($oid.' [');
      foreach ($oid_data['indexes'] as $index => $entry)
      {
        if (empty($entry['oid_num']))
        {
          // Use snmptranslate if oid_num not set
          $entry['oid_num'] = snmp_translate($oid . '.' . $index, $mib);
        }

        $value = snmp_get($device, $entry['oid_num'], '-OQUvs');
        if (is_numeric($value))
        {
          // Fetch description from oid if specified
          if (isset($entry['oid_descr']))
          {
            $entry['descr'] = snmp_get($device, $entry['oid_descr'], '-OQUvs');
          }

          // Check for min/max values, when sensors report invalid data as sensor does not exist
          if ((isset($entry['min']) && $value <= $entry['min']) ||
              (isset($entry['max']) && $value >= $entry['max'])) { continue; }

          // Check limits oids if set
          $limits = array();
          if (isset($entry['oid_limit_low']))       { $limits['limit_low']       = snmp_get($device, $entry['oid_limit_low'], '-OQUvs'); }
          if (isset($entry['oid_limit_low_warn']))  { $limits['limit_low_warn']  = snmp_get($device, $entry['oid_limit_low_warn'], '-OQUvs'); }
          if (isset($entry['oid_limit_high_warn'])) { $limits['limit_high_warn'] = snmp_get($device, $entry['oid_limit_high_warn'], '-OQUvs'); }
          if (isset($entry['oid_limit_high']))      { $limits['limit_high']      = snmp_get($device, $entry['oid_limit_high'], '-OQUvs'); }

          if (!isset($entry['scale'])) { $entry['scale'] = 1; }
          discover_sensor($valid['sensor'], $entry['class'], $device, $entry['oid_num'], $index, $mib . '-' . $oid, $entry['descr'], $entry['scale'], $value, $limits);
        }
      }
      print_cli('] ');
    }
    print_cli("\n");
  }
}

// Detect Status by simple MIB-based discovery :
foreach (get_device_mibs($device) as $mib)
{
  if (is_array($config['mibs'][$mib]['status']))
  {
    print_cli_data_field($mib);
    foreach ($config['mibs'][$mib]['status'] as $oid => $oid_data)
    {
      print_cli($oid.' [');
      foreach ($oid_data['indexes'] as $index => $entry)
      {
        if (empty($entry['oid_num']))
        {
          // Use snmptranslate if oid_num not set
          $entry['oid_num'] = snmp_translate($oid . '.' . $index, $mib);
        }
        $value = snmp_get($device, $entry['oid_num'], '-OQUvsn');
        if (is_numeric($value))
        {
          rename_rrd($device, "status-".$entry['type'].'-'.$index, "status-".$entry['type'].'-'."$oid.$index"); // FIXME. Remove in r8000
          discover_status($device, $entry['oid_num'], "$oid.$index", $entry['type'], $entry['descr'], $value, array('entPhysicalClass' => $entry['measured']));
        }
      }
      print_cli('] ');
    }
    print_cli('] ');
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
