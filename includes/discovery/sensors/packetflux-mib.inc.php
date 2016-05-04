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

echo(" PACKETFLUX-MIB ");

//.1.3.6.1.4.1.32050.2.1.27.1.0 = 0
//.1.3.6.1.4.1.32050.2.1.27.1.1 = 1
//.1.3.6.1.4.1.32050.2.1.27.1.2 = 2
//.1.3.6.1.4.1.32050.2.1.27.1.3 = 3
//.1.3.6.1.4.1.32050.2.1.27.1.4 = 4
//.1.3.6.1.4.1.32050.2.1.27.1.5 = 5
//.1.3.6.1.4.1.32050.2.1.27.1.6 = 6
//.1.3.6.1.4.1.32050.2.1.27.2.0 = "Temperature (0.1C)"
//.1.3.6.1.4.1.32050.2.1.27.2.1 = "Shunt Input (0.1mV)"
//.1.3.6.1.4.1.32050.2.1.27.2.2 = "Power 1 In (0.1V)"
//.1.3.6.1.4.1.32050.2.1.27.2.3 = "Power 2 In (0.1V)"
//.1.3.6.1.4.1.32050.2.1.27.2.4 = "Exp Current (mA)"
//.1.3.6.1.4.1.32050.2.1.27.2.5 = "Relay on Above (0.1C)"
//.1.3.6.1.4.1.32050.2.1.27.2.6 = "Relay on Below (0.1C)"
//.1.3.6.1.4.1.32050.2.1.27.3.0 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.1 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.2 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.3 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.4 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.5 = 0
//.1.3.6.1.4.1.32050.2.1.27.3.6 = 0
//.1.3.6.1.4.1.32050.2.1.27.4.0 = 0
//.1.3.6.1.4.1.32050.2.1.27.4.1 = 1
//.1.3.6.1.4.1.32050.2.1.27.4.2 = 2
//.1.3.6.1.4.1.32050.2.1.27.4.3 = 3
//.1.3.6.1.4.1.32050.2.1.27.4.4 = 4
//.1.3.6.1.4.1.32050.2.1.27.4.5 = 5
//.1.3.6.1.4.1.32050.2.1.27.4.6 = 6
//.1.3.6.1.4.1.32050.2.1.27.5.0 = 303
//.1.3.6.1.4.1.32050.2.1.27.5.1 = -8
//.1.3.6.1.4.1.32050.2.1.27.5.2 = 529
//.1.3.6.1.4.1.32050.2.1.27.5.3 = 531
//.1.3.6.1.4.1.32050.2.1.27.5.4 = 0
//.1.3.6.1.4.1.32050.2.1.27.5.5 = 1000
//.1.3.6.1.4.1.32050.2.1.27.5.6 = -1000
//.1.3.6.1.4.1.32050.2.1.27.6.0 = 0
//.1.3.6.1.4.1.32050.2.1.27.6.1 = 0
//.1.3.6.1.4.1.32050.2.1.27.6.2 = 0
//.1.3.6.1.4.1.32050.2.1.27.6.3 = 0
//.1.3.6.1.4.1.32050.2.1.27.6.4 = 0
//.1.3.6.1.4.1.32050.2.1.27.6.5 = 1000
//.1.3.6.1.4.1.32050.2.1.27.6.6 = -1000

$index_analog = '.1.3.6.1.4.1.32050.2.1.27';
$packetflux_analog = snmpwalk_numericoids($device, $index_analog, array(), 'SNMPv2', mib_dirs());

$oids_analog[0] = array('class' => 'temperature', 'scale' => 0.1);
$oids_analog[1] = array('class' => 'voltage',     'scale' => 0.0001);
$oids_analog[2] = array('class' => 'voltage',     'scale' => 0.1);
$oids_analog[3] = array('class' => 'voltage',     'scale' => 0.1);
$oids_analog[4] = array('class' => 'current',     'scale' => 0.001);
$oids_analog[5] = array('class' => 'temperature', 'scale' => 0.1); // What is this?
$oids_analog[6] = array('class' => 'temperature', 'scale' => 0.1); // What is this?

foreach ($oids_analog as $index => $entry)
{
  $oid = "$index_analog.5.$index";
  if (is_numeric($packetflux_analog[$oid]))
  {
    list($descr) = explode(' (', $packetflux_analog["$index_analog.2.$index"]);
    $class       = $oids_analog[$index]['class'];
    $scale       = $oids_analog[$index]['scale'];
    $value       = $packetflux_analog[$oid];
    discover_sensor($valid['sensor'], $class, $device, $oid, "packetflux-analog-$index", 'packetflux', $descr, $scale, $value);
  }
}

// EOF
