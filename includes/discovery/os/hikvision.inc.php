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

if (!$os)
{
  // HIK-DEVICE-MIB::deviceType.0 = STRING: "DS-2CD2332-I"
  $hw = snmp_get($device, "deviceType.0", "-Osqnv", "HIK-DEVICE-MIB");
  if (strlen($hw))
  {
    $os = "hikvision-cam"; // FIXME. Only Cam detected for now, for other need more checks
    if (preg_match('/^DS\-2C/', $hw)) { $os = "hikvision-cam"; }
  }
}

// EOF
