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

echo(" SensorGateway ");

// FIXME table walk! this looks like ugly old code.
$oids = snmp_walk($device, ".1.3.6.1.4.1.17095.3", "-Osqn");

$oids = trim($oids);
if ($oids)
{
  $index = 0;
  $rows = explode("\n", $oids);
  while ($index < count($rows))
  {
    list($description_oid,$descr) = explode(" ", $rows[$index],2);
    list($temperature_oid,$temperature) = explode(" ", $rows[$index+1],2);
    $limits = array('limit_high' => NULL,
                    'limit_low'  => NULL);

    $descr = str_replace("\"", "", $descr);
    $descr = preg_replace('/ Temp/', "", $descr);
    $descr = trim($descr);
    if ($descr != "-") discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, floor($index/4), 'wut', $descr, 1, $temperature, $limits);

    $index +=4;
  }
}

// EOF
