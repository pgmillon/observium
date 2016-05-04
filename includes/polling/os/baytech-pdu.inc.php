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

$data = snmp_get_multi($device, 'sBTAIdentUnitName.0 sBTAIdentFirmwareRev.0 sBTAIdentSerialNumber.0', '-OQUs', 'Baytech-MIB-403-1');
if (is_array($data[0]))
{
  $hardware = $data[0]['sBTAIdentUnitName'];
  $version  = $data[0]['sBTAIdentFirmwareRev'];
  $serial   = $data[0]['sBTAIdentSerialNumber'];
} else {
  $data = snmp_get_multi($device, 'sBTAModulesRPCName.1 sBTAModulesRPCFirmwareVersion.1', '-OQUs', 'Baytech-MIB-403-1');
  if (is_array($data[1]))
  {
    $hardware = $data[1]['sBTAModulesRPCName'];
    $version  = $data[1]['sBTAModulesRPCFirmwareVersion'];
  }
}

// EOF
