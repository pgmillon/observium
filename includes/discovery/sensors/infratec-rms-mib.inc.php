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

echo(" INFRATEC-RMS-MIB ");

// Unfortunately the MIB is not available to the public currently.

$tempoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.4.1.1.2.1", 'value'=> ".1.3.6.1.4.1.1909.10.4.1.1.3.1" );
$tempoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.4.1.1.2.2", 'value'=> ".1.3.6.1.4.1.1909.10.4.1.1.3.2" );
$tempoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.4.1.1.2.3", 'value'=> ".1.3.6.1.4.1.1909.10.4.1.1.3.3" );
$tempoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.4.1.1.2.4", 'value'=> ".1.3.6.1.4.1.1909.10.4.1.1.3.4" );

foreach ($tempoid as $key => $dummy)
{
  $value = snmp_get($device, $tempoid[$key]['value'], "-Oqv");
  if (is_numeric($value) && $value < 600)
  {
    $descr = snmp_get($device, $tempoid[$key]['descr'], "-Oqv");
    $descr = preg_replace("/\"/","",$descr);
    discover_sensor($valid['sensor'], 'temperature', $device, $tempoid[$key]['value'], $key, 'infratec-rms', $descr, 1, $value);
  }
}

$humoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.5.1.1.2.1", 'value'=> ".1.3.6.1.4.1.1909.10.5.1.1.3.1" );
$humoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.5.1.1.2.2", 'value'=> ".1.3.6.1.4.1.1909.10.5.1.1.3.2" );
$humoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.5.1.1.2.3", 'value'=> ".1.3.6.1.4.1.1909.10.5.1.1.3.3" );
$humoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.5.1.1.2.4", 'value'=> ".1.3.6.1.4.1.1909.10.5.1.1.3.4" );

foreach ($humoid as $key => $dummy)
{
  $value = snmp_get($device, $humoid[$key]['value'], "-Oqv");
  if (is_numeric($value) && $value <= 100)
  {
    $descr = snmp_get($device, $humoid[$key]['descr'], "-Oqv");
    $descr = preg_replace("/\"/","",$descr);
    discover_sensor($valid['sensor'], 'humidity', $device, $humoid[$key]['value'], $key, 'infratec-rms', $descr, 1, $value);
  }
}

$mainsoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.6.1.1.2.1", 'value'=> ".1.3.6.1.4.1.1909.10.6.1.1.3.1" );
$mainsoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.6.1.1.2.2", 'value'=> ".1.3.6.1.4.1.1909.10.6.1.1.3.2" );
$mainsoid[] = array( 'descr'=> ".1.3.6.1.4.1.1909.10.6.1.1.2.3", 'value'=> ".1.3.6.1.4.1.1909.10.6.1.1.3.3" );

foreach ($mainsoid as $key => $dummy)
{
  $value = snmp_get($device, $mainsoid[$key]['value'], "-Oqv");
  if (is_numeric($value) && $value > 0)
  {
    $descr = snmp_get($device, $mainsoid[$key]['descr'], "-Oqv");
    $descr = preg_replace("/\"/","",$descr);
    discover_sensor($valid['sensor'], 'voltage', $device, $mainsoid[$key]['value'], $key, 'infratec-rms', $descr, 1, $value);
  }
}

unset($tempoid, $humoid, $mainsoid);

// EOF
