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

// FIXME needs a rewrite to new scheme, with table walking and MIB use.

echo(" NETBOTZV2-MIB ");

$oids = snmp_walk($device, ".1.3.6.1.4.1.5528.100.4.1.1.1.4", "-Osqn", "");

$oids = trim($oids);
if ($oids)
{
  foreach (explode("\n", $oids) as $data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.', $oid);
    $temperature_id = $split_oid[count($split_oid)-1];
    $temperature_oid = ".1.3.6.1.4.1.5528.100.4.1.1.1.8.$temperature_id";
    $temperature = snmp_get($device, $temperature_oid, "-Ovq");
    $descr = str_replace("\"", "", $descr);
    $descr = preg_replace('/Temperature  /', "", $descr);
    $descr = trim($descr);
    if ($temperature != "0" && $temperature <= "1000")
    {
      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'netbotz', $descr, 1, $temperature);
    }
  }
}

$oids = snmp_walk($device, ".1.3.6.1.4.1.5528.100.4.1.2.1.4", "-Osqn", "");

$oids = trim($oids);
if ($oids)
{
  foreach (explode("\n", $oids) as $data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.',$oid);
    $humidity_id = $split_oid[count($split_oid)-1];
    #tempHumidSensorHumidValue
    $humidity_oid = ".1.3.6.1.4.1.5528.100.4.1.2.1.8.".$humidity_id;
    $humidity = snmp_get($device,"$humidity_oid", "-Ovq", "");
    $descr = str_replace("\"", "", $descr);
    $descr = trim($descr);
    if ($humidity >= 0)
    {
      discover_sensor($valid['sensor'], 'humidity', $device, $humidity_oid, $humidity_id, 'netbotz', $descr, 1, $humidity);
    }
  }
  unset($data);
}

unset($oids);

// EOF
