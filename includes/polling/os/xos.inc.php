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

// retrieve oid data
$oid_list = "extremeImageBooted.0 extremePrimarySoftwareRev.0 extremeSecondarySoftwareRev.0 extremeSystemID.0 sysObjectID.0 extremeImageSshCapability.cur extremeImageUAACapability.cur";
$data = snmp_get_multi($device, $oid_list, "-OUQs", "EXTREME-BASE-MIB", mib_dirs('extreme'));

// hardware platform
$hardware = $data[0]['sysObjectID'];
$hardware = rewrite_extreme_hardware($hardware);

// determine running firmware version
switch ($data[0]['extremeImageBooted'])
{
  case "primary":
    $version = $data[0]['extremePrimarySoftwareRev'];
    break;
  case "secondary":
    $version = $data[0]['extremeSecondarySoftwareRev'];
    break;
  default:
    $version = "UNKNOWN";
}

// serial number
$serial = $data[0]['extremeSystemID'];

// features
$features = "";
if ($data['cur']['extremeImageSshCapability'] <> "unknown" && trim($data['cur']['extremeImageSshCapability'] <> ""))
{
  $features .= " " . $data['cur']['extremeImageSshCapability'];
}

if ($data['cur']['extremeImageUAACapability'] <> "unknown" && trim($data['cur']['extremeImageUAACapability'] <> ""))
{
  $features .= " " . $data['cur']['extremeImageUAACapability'];
}

// cleanup
$serial = str_replace("\"","", $serial);
$version = str_replace("\"","", $version);
$features = trim(str_replace("\"","", $features));
$hardware = str_replace("\"","", $hardware);

echo sprintf("Extreme Networks: Hardware: %s, Serial: %s, Version: %s, Features: %s", $hardware, $serial, $version, $features);

// EOF
