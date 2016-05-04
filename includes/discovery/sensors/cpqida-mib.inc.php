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

echo(" CPQIDA-MIB ");

// Controllers

$oids = snmpwalk_cache_oid($device, 'cpqDaCntlrEntry', array(), 'CPQIDA-MIB', mib_dirs('hp'));

foreach ($oids as $index => $entry)
{
  if (isset($entry['cpqDaCntlrBoardStatus']))
  {
    $hardware   = rewrite_cpqida_hardware($entry['cpqDaCntlrModel']);
    $descr      = $hardware.' ('.$entry['cpqDaCntlrHwLocation'].') Status';
    $oid        = ".1.3.6.1.4.1.232.3.2.2.1.1.10.".$index;
    $status     = $entry['cpqDaCntlrBoardStatus'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqDaCntlrEntry'.$index, 'cpqida-cntrl-state', $descr, NULL, $status, array('entPhysicalClass' => 'controller'));

    if ($entry['cpqDaCntlrCurrentTemp'] > 0)
    {
      $oid       = ".1.3.6.1.4.1.232.3.2.2.1.1.32.".$index;
      $value     = $entry['cpqDaCntlrCurrentTemp'];
      $descr     = $hardware.' ('.$entry['cpqDaCntlrHwLocation'].')';
      discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'cpqDaCntlrEntry'.$index, 'cpqida-cntrl-temp', $descr, 1, $value);
    }
  }
}

// Physical Disks

$oids = snmpwalk_cache_oid($device, 'cpqDaPhyDrv', array(), 'CPQIDA-MIB', mib_dirs('hp'));

foreach ($oids as $index => $entry)
{
  if ($entry['cpqDaPhyDrvTemperatureThreshold'] > 0)
  {
    $descr      = "HDD ".$entry['cpqDaPhyDrvBay'];
    $oid        = ".1.3.6.1.4.1.232.3.2.5.1.1.70.".$index;
    $value      = $entry['cpqDaPhyDrvCurrentTemperature'];
    $limits     = array('limit_high' => $entry['cpqDaPhyDrvTemperatureThreshold']);

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'cpqDaPhyDrv.'.$index, 'cpqida', $descr, 1, $value, $limits);
  }

  if (isset($entry['cpqDaPhyDrvSmartStatus']))
  {
    $descr      = $descr." SMART Status";
    $oid        = ".1.3.6.1.4.1.232.3.2.5.1.1.57.".$index;
    $status     = $entry['cpqDaPhyDrvSmartStatus'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqDaPhyDrv.'.$index, 'cpqida-smart-state', $descr, NULL, $status, array('entPhysicalClass' => 'other'));
  }
}

// Logical Disks

$oids = snmpwalk_cache_oid($device, 'cpqDaLogDrv', array(), 'CPQIDA-MIB', mib_dirs('hp'));

foreach ($oids as $index => $entry)
{
  if (isset($entry['cpqDaLogDrvCondition']))
  {
    $descr      = "Logical Drive ".$entry['cpqDaLogDrvIndex'].' ('.$entry['cpqDaLogDrvOsName'].') Status';
    $oid        = ".1.3.6.1.4.1.232.3.2.3.1.1.11.".$index;
    $status     = $entry['cpqDaLogDrvCondition'];

    discover_sensor($valid['sensor'], 'state', $device, $oid, 'cpqDaLogDrv.'.$index, 'cpqida-smart-state', $descr, NULL, $status, array('entPhysicalClass' => 'other'));
  }
}

// EOF
