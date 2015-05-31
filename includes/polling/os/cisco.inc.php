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

if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([\w\d]+)-\w\), Version ([^,]+)/', $poll_device['sysDescr'], $regexp_result))
{
  $features = $regexp_result[1];
  $version = $regexp_result[2];
}

if ($entPhysical['entPhysicalContainedIn'] === '0')
{
  if (!empty($entPhysical['entPhysicalSoftwareRev']))
  {
    $version = $entPhysical['entPhysicalSoftwareRev'];
  }
  if (!empty($entPhysical['entPhysicalModelName']))
  {
    $hardware = $entPhysical['entPhysicalModelName'];
  } else {
    $hardware = $entPhysical['entPhysicalName'];
  }
}

#   if ($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
#   if (($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
#   if ($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
#   $ciscomodel = str_replace("\"","",$ciscomodel);
#   if ($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }

if(empty($hardware)) {   $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs(array("cisco"))); }

#if(isset($cisco_hardware_oids[$poll_device['sysObjectID']])) { $hardware = $cisco_hardware_oids[$poll_device['sysObjectID']]; }

// EOF
