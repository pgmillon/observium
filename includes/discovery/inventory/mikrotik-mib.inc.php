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

echo("MIKROTIK-MIB ");

$mtxrSerialNumber = snmp_get($device, 'mtxrSerialNumber.0', '-Oqvn', 'MIKROTIK-MIB', mib_dirs('mikrotik'));

$system_index = 1;
if ($mtxrSerialNumber)
{
  $inventory[$system_index] = array(
    'entPhysicalDescr'        => 'MikroTik RouterBoard',
    'entPhysicalClass'        => 'chassis',
    'entPhysicalName'         => '',
    'entPhysicalSerialNum'    => $mtxrSerialNumber,
    'entPhysicalAssetID'      => '',
    'entPhysicalIsFRU'        => 'false',
    'entPhysicalContainedIn'  => 0,
    'entPhysicalParentRelPos' => 0,
    'entPhysicalMfgName'      => 'MikroTik'
  );
  discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], "MIKROTIK-MIB");

  if (OBS_DEBUG > 1 && count($inventory)) { print_vars($inventory); }
}

echo(PHP_EOL);

// EOF
