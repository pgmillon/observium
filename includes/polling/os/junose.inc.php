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

if (strpos($poll_device['sysDescr'], "olive"))
{
  $hardware = "Olive";
  $serial = "";
}
else
{
  $hardware = "Juniper " . rewrite_junos_hardware($poll_device['sysObjectID']);
  $junose_version   = snmp_get($device, "juniSystemSwVersion.0", "-Ovqs", "Juniper-System-MIB", mib_dirs("junose"));
  $junose_serial    = "";
}

list($version) = explode(" ", $junose_version);
list(,$version) =  explode("(", $version);
list($features) = explode("]", $junose_version);
list(,$features) =  explode("[", $features);

// EOF
