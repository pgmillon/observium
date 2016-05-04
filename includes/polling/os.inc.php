<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// Cache hardware/version/serial info from ENTITY-MIB (if possible use inventory module data)
if (in_array($device['os_group'], array('unix', 'cisco')) || in_array($device['os'], array('acme', 'nos', 'ibmnos', 'acsw', 'fabos', 'wlc')))
{
  // Get entPhysical tables for some OS and OS groups
  if ($config['discovery_modules']['inventory'])
  {
    $entPhysical = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalContainedIn` = ?', array($device['device_id'], '0'));
  } else {
    switch (TRUE)
    {
      case ($device['os_group'] == 'cisco' || $device['os'] == 'acsw'):
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
  // OS Specific
  include($config['install_dir'] . "/includes/polling/os/".$device['os'].".inc.php");
}
else if ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php"))
{
  // OS Group Specific
  include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
} else {
  print_message("I'm Generic :'(");
}

print_message("\nHardware: ".$hardware." Version: ".$version." Features: ".$features." Serial: ".$serial." Asset: ".$asset_tag);

unset($entPhysical, $oids, $hw);

// EOF
