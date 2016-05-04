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

echo(" ASYNCOS-MAIL-MIB ");

$oids = snmpwalk_cache_oid($device, "fanTable", array(), "ASYNCOS-MAIL-MIB", mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr = $entry['fanName'];
  $oid   = ".1.3.6.1.4.1.15497.1.1.1.10.1.2.$index";
  $value = $entry['fanRPMs'];

  if (is_numeric($entry['fanRPMs']))
  {
    discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'asyncos-fan', $descr, 1, $value);
  }
}

$oids = snmpwalk_cache_oid($device, "temperatureTable", array(), "ASYNCOS-MAIL-MIB", mib_dirs('cisco'));

foreach ($oids as $index => $entry)
{
  $descr  = $entry['temperatureName'];
  $oid    = ".1.3.6.1.4.1.15497.1.1.1.9.1.2.".$index;
  $value  = $entry['degreesCelcius'];
  $limits = array('limit_high' => 45,
                  'limit_low'  => 10);
  if (is_numeric($entry['degreesCelcius']))
  {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'asyncos-temp', $descr, 1, $value, $limits);
  }
}

// EOF
