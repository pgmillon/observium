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

/// FIXME - rewrite this so it uses SNMPWALK to get descr and temperature.

echo(" JUNIPER-MIB ");

$oids = snmp_walk($device,"1.3.6.1.4.1.2636.3.1.13.1.7", "-Osqn","JUNIPER-MIB", mib_dirs('juniper'));
$oids = trim($oids);

foreach (explode("\n", $oids) as $data)
{
  $data = trim($data);
  $data = substr($data, 29);
  if ($data)
  {
    list($oid) = explode(" ", $data);
    $temperature_oid  = "1.3.6.1.4.1.2636.3.1.13.1.7.$oid";
    $descr_oid = "1.3.6.1.4.1.2636.3.1.13.1.5.$oid";
    $descr = snmp_get($device, $descr_oid, "-Oqv", "JUNIPER-MIB", mib_dirs('juniper'));
    $temperature = snmp_get($device, $temperature_oid, "-Oqv", "JUNIPER-MIB", mib_dirs('juniper'));
    if (!strstr($descr, "No") && !strstr($temperature, "No") && $descr != "" && $temperature != "0")
    {
      $descr = str_replace("\"", "", $descr);
      $descr = str_replace("temperature", "", $descr);
      $descr = str_replace("temperature", "", $descr);
      $descr = str_replace("sensor", "", $descr);
      $descr = trim($descr);

      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'junos', $descr, 1, $temperature);
    }
  }
}

// EOF
