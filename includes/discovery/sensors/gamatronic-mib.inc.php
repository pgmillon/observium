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

# FIXME - Needs a rewrite; discovered oids should not be textual, instead of for loops, use table walks if possible. -TL

echo(" GAMATRONIC-MIB ");

$type        = "gamatronicups";
$limits      = array('limit_low' => 0);

for ($i = 1; $i <= 3; $i++)
{
  $descr = "Input Phase $i";
  $oid   = ".1.3.6.1.4.1.6050.5.4.1.1.3.$i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = $i;

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, $type, $descr, 1, $value, $limits);
  }
}

for ($i = 1; $i <= 3; $i++)
{
  $descr = "Output Phase $i";
  $oid   = ".1.3.6.1.4.1.6050.5.5.1.1.3.$i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = 100+$i;

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, $type, $descr, 1, $value, $limits);
  }
}

for($i = 1; $i <= 3 ;$i++)
{
  $descr = "Input Phase $i";
  $oid   = ".1.3.6.1.4.1.6050.5.4.1.1.2.$i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = $i;

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, 1, $value);
  }
}

for($i = 1; $i <= 3 ;$i++)
{
  $descr = "Output Phase $i";
  $oid   = ".1.3.6.1.4.1.6050.5.5.1.1.2.$i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = 100+$i;

  if (is_numeric($value))
  {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, 1, $value);
  }
}

// EOF
