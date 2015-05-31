<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

# FIXME - consolidated 4 files, but could most certainly do with a rewrite; weird 3-phase-only non-table-walking code ahead! -TL

echo(" SICONUPS-MIB ");

$scale  = 0.1;
$limits = array('limit_low' => 0);
for ($i = 1; $i <= 3; $i++)
{
  $oid   = "1.3.6.1.4.1.4555.1.1.1.1.3.3.1.3.$i";
  $descr = "Input Phase $i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = $i;

  discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale, $limits);
}

for ($i = 1; $i <= 3; $i++)
{
  $oid   = "1.3.6.1.4.1.4555.1.1.1.1.4.4.1.3.$i";
  $descr = "Output Phase $i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = 100+$i;

  discover_sensor($valid['sensor'], 'current', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale, $limits);
}

$oid   = "1.3.6.1.4.1.4555.1.1.1.1.3.2.0";
$descr = "Input";
$value = snmp_get($device, $oid, "-Oqv");
$index = '3.2.0';
discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale);

$oid   = "1.3.6.1.4.1.4555.1.1.1.1.4.2.0";
$descr = "Output";
$value = snmp_get($device, $oid, "-Oqv");
$index = '4.2.0';
discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale);

// Battery voltage
$oid   = "1.3.6.1.4.1.4555.1.1.1.1.2.5.0";
$descr = "Battery";
$value = snmp_get($device, $oid, "-Oqv");
$index = 200;

discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale);

for ($i = 1; $i <= 3 ;$i++)
{
  $oid   = "1.3.6.1.4.1.4555.1.1.1.1.3.3.1.2.$i";
  $descr = "Input Phase $i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = $i;

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale);
}

for ($i = 1; $i <= 3 ;$i++)
{
  $oid   = "1.3.6.1.4.1.4555.1.1.1.1.4.4.1.2.$i";
  $descr = "Output Phase $i";
  $value = snmp_get($device, $oid, "-Oqv");
  $index = 100+$i;

  discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'netvision', $descr, $scale, $value * $scale);
}

// EOF
