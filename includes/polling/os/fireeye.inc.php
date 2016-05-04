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

//FE-FIREEYE-MIB::feInstalledSystemImage.0 = STRING: "CMS (CMS) 7.2.0.224371"
//FE-FIREEYE-MIB::feSystemImageVersionCurrent.0 = STRING: "7.2.0"
//FE-FIREEYE-MIB::feSecurityContentVersion.0 = STRING: "361.121"
//if (empty($version)) // FIXME. What?
//{
  $features = snmp_get($device, "feInstalledSystemImage.0", "-Osqv", "FE-FIREEYE-MIB");
  $content = snmp_get($device, "feSecurityContentVersion.0", "-Osqv", "FE-FIREEYE-MIB");
  $features = trim(str_replace("\"","", "$features ($content)"));

  $version = snmp_get($device, "feSystemImageVersionCurrent.0", "-Osqv", "FE-FIREEYE-MIB");
  $version = trim(str_replace("\"","", $version));
//}

//FE-FIREEYE-MIB::feHardwareModel.0 = STRING: "FireEyeCMS4400"
//FE-FIREEYE-MIB::feSerialNumber.0 = STRING: "FM1419CA02Y"
$hardware = snmp_get($device, "feHardwareModel.0", "-Osqv", "FE-FIREEYE-MIB");
$hardware = substr($hardware, 1, -1);

$serial = snmp_get($device, "feSerialNumber.0", "-Osqv", "FE-FIREEYE-MIB");
$serial = substr($serial, 1, -1);

// EOF
