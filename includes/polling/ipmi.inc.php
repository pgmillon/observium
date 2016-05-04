<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

global $ipmi_sensors;

include_once("includes/polling/functions.inc.php");

/// FIXME. From this uses only check_valid_sensors(), maybe need move to global functions or copy to polling. --mike
include_once("includes/discovery/functions.inc.php");

if ($ipmi['host'] = get_dev_attrib($device,'ipmi_hostname'))
{
  $ipmi['user']      = get_dev_attrib($device,'ipmi_username');
  $ipmi['password']  = get_dev_attrib($device,'ipmi_password');
  $ipmi['port']      = get_dev_attrib($device,'ipmi_port');
  $ipmi['interface'] = get_dev_attrib($device,'ipmi_interface');
  $ipmi['userlevel'] = get_dev_attrib($device,'ipmi_userlevel');

  if (!is_numeric($ipmi['port'])) { $ipmi['port'] = 623; }
  if ($ipmi['userlevel'] == '') { $ipmi['userlevel'] = 'USER'; }

  if (array_search($ipmi['interface'],array_keys($config['ipmi']['interfaces'])) === FALSE) { $ipmi['interface'] = 'lan'; } // Also triggers on empty value

  if ($config['own_hostname'] != $device['hostname'] || $ipmi['host'] != 'localhost')
  {
    $remote = " -I " . escapeshellarg($ipmi['interface']) . " -p " . $ipmi['port'] . " -H " . escapeshellarg($ipmi['host']) . " -L " . escapeshellarg($ipmi['userlevel']) . " -U " . escapeshellarg($ipmi['user']) . " -P " . escapeshellarg($ipmi['password']);
  }

  $results = external_exec($config['ipmitool'] . $remote . " sensor 2>/dev/null");

  $ipmi_sensors = parse_ipmitool_sensor($device, $results);
}

if (OBS_DEBUG) { print_vars($ipmi_sensors); }

foreach ($config['ipmi_unit'] as $type)
{
  check_valid_sensors($device, $type, $ipmi_sensors, 'ipmi');
}

// EOF
