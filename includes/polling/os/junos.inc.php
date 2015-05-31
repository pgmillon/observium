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

$jun_ver = snmp_get($device, "hrSWInstalledName.2", "-Oqv", "HOST-RESOURCES-MIB");

if (strpos($poll_device['sysDescr'], "olive"))
{
  $hardware = "Olive";
  $serial = "";
} else {
  $hardware = snmp_get($device, "JUNIPER-MIB::jnxBoxDescr.0", "-Ovqsn", "JUNIPER-MIB", mib_dirs("junos"));
#  $hardware = "Juniper " . rewrite_junos_hardware($hardware);
  $serial   = snmp_get($device, "JUNIPER-MIB::jnxBoxSerialNo.0", "-OQv", "JUNIPER-MIB", mib_dirs("junos"));
}

list($version) = explode("]", $jun_ver);
list(,$version) =  explode("[", $version);
$features = "";

// EOF
