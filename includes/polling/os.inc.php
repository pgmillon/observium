<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (in_array($device['os_group'], array('unix', 'cisco')) || in_array($device['os'], array('acme', 'nos', 'acsw', 'fabos', 'wlc')))
{
  // Get entPhysical tables for some OS and OS groups
  if ($config['discovery_modules']['inventory'])
  {
    $entPhysical = dbFetchRow('SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?', array($device['device_id'], '1'));
  } else {
    if ($device['os_group'] == 'cisco' || $device['os'] == 'acsw')
    {
      $oids = 'entPhysicalDescr.1 entPhysicalSerialNum.1 entPhysicalModelName.1 entPhysicalContainedIn.1 entPhysicalName.1 entPhysicalSoftwareRev.1';
    }
    elseif ($device['os'] == 'qnap')
    {
      $oids = 'entPhysicalDescr.1 entPhysicalName.1 entPhysicalSerialNum.1 entPhysicalFirmwareRev.1';
    }
    elseif ($device['os'] == 'wlc')
    {
      $oids = 'entPhysicalDescr.1 entPhysicalModelName.1 entPhysicalSoftwareRev.1';
    } else {
      $oids = 'entPhysicalDescr.1 entPhysicalSerialNum.1';
    }
    $data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB', mib_dirs());
    $entPhysical = $data[1];
  }

  if (!empty($entPhysical['entPhysicalDescr']))
  {
    $entPhysical['entPhysicalDescr'] = str_replace(array(' Inc.', ' Computer Corporation', ' Corporation'), '', $entPhysical['entPhysicalDescr']);
    $entPhysical['entPhysicalDescr'] = str_replace('IBM IBM', 'IBM', $entPhysical['entPhysicalDescr']);

    if (strpos($entPhysical['entPhysicalSerialNum'], '..') === FALSE)
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
elseif ($device['os_group'] && is_file($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php"))
{
  // OS Group Specific
  include($config['install_dir'] . "/includes/polling/os/".$device['os_group'].".inc.php");
}
else
{
  print_message("I'm Generic :'(");
}

echo("\nHardware: ".$hardware." Version: ".$version." Features: ".$features." Serial: ".$serial." Asset: ".$asset_tag."\n");

unset($entPhysical, $oids, $hw);

// EOF
