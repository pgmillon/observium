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

echo(" WebGraph-8xThermometer-US-MIB ");

// FIXME, rewrite!

$oids = snmp_walk($device, ".1.3.6.1.4.1.5040.1.2.6.3.2.1.1.1", "-Osqn", "us_an8graph_mib_130.mib");

$oids = trim($oids);
if ($oids)
{
  foreach (explode("\n", $oids) as $data)
  {
    list($oid,$descr) = explode(" ", $data,2);
    $split_oid = explode('.', $oid);
    $temperature_id = $split_oid[count($split_oid)-1];
    $temperature_oid = ".1.3.6.1.4.1.5040.1.2.6.1.3.1.1.$temperature_id";
    $temperature = snmp_get($device, $temperature_oid, "-Ovq");
    $descr = str_replace("\"", "", $descr);
    $descr = preg_replace('/Temperature  /', "", $descr);
    $descr = trim($descr);
    $limit_high_oid = ".1.3.6.1.4.1.5040.1.2.6.3.1.5.3.1.3.$temperature_id";
    $limit_low_oid  = ".1.3.6.1.4.1.5040.1.2.6.3.1.5.3.1.2.$temperature_id";
    $limits = array('limit_high' => floatval(trim(snmp_get($device, $limit_high_oid, "-Oqv", ""),'"')),
                    'limit_low'  => floatval(trim(snmp_get($device, $limit_low_oid, "-Oqv", ""),'"')));

    discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'wut', $descr, 1, $temperature, $limits);
  }
}

// EOF
