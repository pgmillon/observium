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

// Cache hardware/version/serial info from ENTITY-MIB (if possible use inventory module data)
if (is_device_mib($device, 'ENTITY-MIB') &&
    (in_array($device['os_group'], array('unix', 'cisco')) ||
     in_array($device['os'], array('acme', 'nos', 'ibmnos', 'acsw', 'fabos', 'wlc', 'h3c', 'hh3c'))))
{
  // Get entPhysical tables for some OS and OS groups
  if ($config['discovery_modules']['inventory'])
  {
    $entPhysical = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalContainedIn` = ?', array($device['device_id'], '0'));
  } else {
    switch (TRUE)
    {
      case ($device['os_group'] == 'cisco' || in_array($device['os'], array('acme', 'h3c', 'hh3c'))):
        $oids = 'entPhysicalDescr.1 entPhysicalSerialNum.1 entPhysicalModelName.1 entPhysicalContainedIn.1 entPhysicalName.1 entPhysicalSoftwareRev.1';
        break;
      case ($device['os'] == 'qnap'):
        $oids = 'entPhysicalDescr.1 entPhysicalName.1 entPhysicalSerialNum.1 entPhysicalFirmwareRev.1';
        break;
      case ($device['os'] == 'ibmnos'):
        $oids = 'entPhysicalName.1 entPhysicalSerialNum.1 entPhysicalSoftwareRev.1';
        break;
      case ($device['os'] == 'wlc'):
        $oids = 'entPhysicalDescr.1 entPhysicalModelName.1 entPhysicalSoftwareRev.1';
        break;
      default:
        $oids = 'entPhysicalDescr.1 entPhysicalSerialNum.1';
    }
    $data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB', mib_dirs());
    $entPhysical = $data[1];
  }

  if (!empty($entPhysical['entPhysicalDescr']))
  {
    $entPhysical['entPhysicalDescr'] = str_replace(array(' Inc.', ' Computer Corporation', ' Corporation'), '', $entPhysical['entPhysicalDescr']);
    $entPhysical['entPhysicalDescr'] = str_replace('IBM IBM', 'IBM', $entPhysical['entPhysicalDescr']);

    if (strpos($entPhysical['entPhysicalSerialNum'], '..') !== FALSE)
    {
      $entPhysical['entPhysicalSerialNum'] = '';
    }
  } else {
    unset($entPhysical);
  }
}

if (is_file($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php"))
{
  print_cli_data("OS Poller", 'OS', 2);
  // OS Specific
  include($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php");
}
else if ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php"))
{
  // OS Group Specific
  print_cli_data("OS Poller", 'Group', 2);

  include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
} else {
  print_cli_data("OS Poller", '%rGeneric%w', 2);
}

print_cli_data("Hardware", ($hardware ?: "%b<empty>%n"));
print_cli_data("Version", ($version ?: "%b<empty>%n"));
print_cli_data("Features", ($features ?: "%b<empty>%n"));
print_cli_data("Serial", ($serial ?: "%b<empty>%n"));
print_cli_data("Asset", ($asset_tag ?: "%b<empty>%n"));

echo(PHP_EOL);
foreach ($os_additional_info as $header => $entries)
{
  print_cli_heading($header, 3);
  foreach ($entries as $field => $entry)
  {
    print_cli_data($field, $entry, 3);
  }
  echo(PHP_EOL);
}

// Fields notified in event log
$update_fields = array('version', 'features', 'hardware', 'serial', 'kernel', 'distro', 'distro_ver', 'arch', 'asset_tag');

// Log changed variables
foreach ($update_fields as $field)
{
  if (isset($$field)) { $$field = snmp_fix_string($$field); } // Fix unprintable chars

  if ((isset($$field) || strlen($device[$field])) && $$field != $device[$field])
  {
    $update_array[$field] = $$field;
    log_event(nicecase($field)." -> ".$update_array[$field], $device, 'device', $device['device_id']);
  }
}

// Here additional fields, change only if not set already
foreach (array('type', 'icon') as $field)
{
  if (isset($$field) && ($device[$field] == "unknown" || $device[$field] == '' || !isset($device[$field]) || !strlen($device[$field])))
  {
    $update_array[$field] = $$field;
    log_event(nicecase($field)." -> ".$update_array[$field], $device, 'device', $device['device_id']);
  }
}

unset($entPhysical, $oids, $hw, $os_additional_info);

// EOF
