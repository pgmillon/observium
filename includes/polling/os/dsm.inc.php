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

// SYNOLOGY-SYSTEM-MIB::version.0 = STRING: "DSM 4.2-3202"

// SYNOLOGY-SYSTEM-MIB::modelName.0 = STRING: "DS1513+"
// SYNOLOGY-SYSTEM-MIB::serialNumber.0 = STRING: "13A0LNN000123"
// SYNOLOGY-SYSTEM-MIB::version.0 = STRING: "DSM 5.0-4458"

$hardware = snmp_get($device, 'modelName.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology'));
if ($hardware)
{
  $version  = snmp_get($device, 'version.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology'));
  $serial   = snmp_get($device, 'serialNumber.0', '-OQv', 'SYNOLOGY-SYSTEM-MIB', mib_dirs('synology'));

  $version = str_replace('DSM', '', $version);
} else {
  // HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0 = STRING: "console=ttyS0,115200 ip=off initrd=0x00800040,4M root=/dev/md0 rw syno_hw_version=DS207+v10 ihd_num=2
  $hw = snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv", "HOST-RESOURCES-MIB", mib_dirs());
  if (preg_match('/syno_hw_version=(?<hardware>[^\s\+]+)/', $hw, $matches))
  {
    $hardware = $matches['hardware'];
  }
}

// EOF
