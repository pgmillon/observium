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

echo(" NAS-MIB ");

$oids = snmpwalk_cache_oid($device, "SystemFanTable", array(), "NAS-MIB", mib_dirs('qnap'));

foreach ($oids as $index => $entry)
{
  $descr   = $entry['SysFanDescr'];
  $oid     = ".1.3.6.1.4.1.24681.1.2.15.1.3.$index";
  $value   = $entry['SysFanSpeed'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, "SysFanSpeed.$index", 'nas-mib', $descr, 1, $value);
  }
}

$oids = snmpwalk_cache_oid($device, "SystemHdTable", array(), "NAS-MIB", mib_dirs('qnap'));

foreach ($oids as $index => $entry)
{

  // NAS-MIB::HdDescr.1 = STRING: "HDD1"
  // NAS-MIB::HdModel.1 = STRING: "WD2002FYPS-01U1B"

  $descr   = $entry['HdDescr'] . ': ' . $entry['HdModel'];

  // NAS-MIB::HdStatus.1 = INTEGER: rwError(-9)
  // NAS-MIB::HdStatus.2 = INTEGER: ready(0)

  $oid     = ".1.3.6.1.4.1.24681.1.2.11.1.4.$index";
  $value   = $entry['HdStatus'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'state', $device, $oid, "HdStatus.$index", 'nas-mib-hd-state', $descr, 1, $value);
  }

  // NAS-MIB::HdTemperature.1 = STRING: "36 C/96 F"
  // WTF, QNAP.

  $oid     = ".1.3.6.1.4.1.24681.1.2.11.1.3.$index";
  $value   = $entry['HdTemperature'];

  if ($value != '')
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "HdTemperature.$index", 'nas-mib', $descr, 1, $value);
  }
}

// EOF
