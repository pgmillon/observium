<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/// FIXME. Full rewrite and move to inventory stuff

echo("HOST-RESOURCES-MIB ");

$hrDevice_oids = array('hrDeviceEntry','hrProcessorEntry');

$hrDevices = array();
foreach ($hrDevice_oids as $oid) { $hrDevices = snmpwalk_cache_oid($device, $oid, $hrDevices, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }
if (OBS_DEBUG > 1 && count($hrDevices)) { print_vars($hrDevices); }

if (is_array($hrDevices))
{
  $hrDevices = $hrDevices;
  foreach ($hrDevices as $hrDevice)
  {
    if (is_array($hrDevice) && is_numeric($hrDevice['hrDeviceIndex']))
    {
      if (dbFetchCell("SELECT COUNT(*) FROM `hrDevice` WHERE device_id = ? AND hrDeviceIndex = ?",array($device['device_id'], $hrDevice['hrDeviceIndex'])))
      {
        if (($hrDevice['hrDeviceType'] == "hrDeviceProcessor") && empty($hrDevice['hrDeviceDescr'])) { $hrDevice['hrDeviceDescr'] = "Processor"; }
        $update_array = array('hrDeviceType'   => $hrDevice['hrDeviceType'],
                              'hrDeviceDescr'  => $hrDevice['hrDeviceDescr'],
                              'hrDeviceStatus' => $hrDevice['hrDeviceStatus'],
                              'hrDeviceErrors' => $hrDevice['hrDeviceErrors']);

        if ($hrDevice['hrDeviceType'] == "hrDeviceProcessor")
        {
          $update_array['hrProcessorLoad'] = $hrDevice['hrProcessorLoad'];
        } else {
          $update_array['hrProcessorLoad'] = array('NULL');
        }
        dbUpdate($update_array, 'hrDevice', 'device_id = ? AND hrDeviceIndex = ?', array($device['device_id'], $hrDevice['hrDeviceIndex']));
        // FIXME -- check if it has updated, and print a U instead of a .
        echo(".");
      } else {
        $inserted = dbInsert(array('hrDeviceIndex' => $hrDevice['hrDeviceIndex'], 'device_id' => $device['device_id'], 'hrDeviceType' => $hrDevice['hrDeviceType'], 'hrDeviceDescr' => $hrDevice['hrDeviceDescr'], 'hrDeviceStatus' => $hrDevice['hrDeviceStatus'], 'hrDeviceErrors' => $hrDevice['hrDeviceErrors']), 'hrDevice');
        echo("+");
      }
      $valid_hrDevice[$hrDevice['hrDeviceIndex']] = 1;
    }
  }
}

foreach (dbFetchRows('SELECT * FROM `hrDevice` WHERE `device_id`  = ?', array($device['device_id'])) as $test_hrDevice)
{
  if (!$valid_hrDevice[$test_hrDevice['hrDeviceIndex']])
  {
    $deleted = dbDelete('hrDevice', '`hrDevice_id` = ?', array($test_hrDevice['hrDevice_id']));
    echo("-");
    if (OBS_DEBUG > 1) { print_vars($test_hrDevice); echo($deleted . " deleted"); }
  }
}

unset($valid_hrDevice);

// EOF
