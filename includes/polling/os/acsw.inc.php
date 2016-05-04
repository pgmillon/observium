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

// FIXME. I'm not sure that it works correctly
// The ACE I could find have sysDescr "ACEdirector 3", so most of the sysDescr magic below won't do anything.
// Hardware through sysObjectID works though.

$poll_device['sysDescr'] = str_replace("IOS (tm)", "IOS (tm),", $poll_device['sysDescr']);
$poll_device['sysDescr'] = str_replace(")  RELEASE", "), RELEASE", $poll_device['sysDescr']);

echo("\n".$poll_device['sysDescr']."\n");

list(,$features,$version) = explode(",", $poll_device['sysDescr']);

$version = str_replace(" Version ", "", $version);
list(,$features) = explode("(", $features);
list(,$features) = explode("-", $features);

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

list($version) = explode(",", $version);

#   if ($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
#   if (($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
#   if ($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
#   $ciscomodel = str_replace("\"","",$ciscomodel);
#   if ($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }

if ($hardware == "") { $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB:ALTEON-ROOT-MIB", mib_dirs('cisco','alteon')); }

#if(isset($cisco_hardware_oids[$poll_device['sysObjectID']])) { $hardware = $cisco_hardware_oids[$poll_device['sysObjectID']]; }

if (strpos($poll_device['sysDescr'], "IOS XR"))
{
  list(,$version) = explode(",", $poll_device['sysDescr']);
  $version = trim($version);
  list(,$version) = explode(" ", $version);
  list($version) = explode("\n", $version);
  trim($version);
}

// EOF
