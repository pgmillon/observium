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

/*
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.1 = Gauge32: 1
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.11 = Gauge32: 11
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.21 = Gauge32: 21
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.31 = Gauge32: 31
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.41 = Gauge32: 41
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.42 = Gauge32: 42
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.43 = Gauge32: 43
MSERIES-ENVMON-MIB::smartEnvMonTemperatureIndex.44 = Gauge32: 44
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.1 = STRING: Chassis
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.11 = STRING: NMB
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.21 = STRING: PSU1
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.31 = STRING: PSU2
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.41 = STRING: Fan (Sensor 1)
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.42 = STRING: Fan (Sensor 2)
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.43 = STRING: Fan (Sensor 3)
MSERIES-ENVMON-MIB::smartEnvMonTemperatureDescr.44 = STRING: Fan (Sensor 4)
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.1 = INTEGER: 31 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.11 = INTEGER: 40 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.21 = INTEGER: 38 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.31 = INTEGER: 36 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.41 = INTEGER: 30 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.42 = INTEGER: 31 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.43 = INTEGER: 32 degrees Celsius
MSERIES-ENVMON-MIB::smartEnvMonTemperatureValue.44 = INTEGER: 32 degrees Celsius
*/

echo(' MSERIES-ENVMON-MIB ');

$oids = array();
$todo = array('smartEnvMonTemperatureDescr', 'smartEnvMonTemperatureValue');
foreach ($todo as $table)
{
  $oids = snmpwalk_cache_oid($device, $table, $oids, 'MSERIES-ENVMON-MIB', mib_dirs('smartoptics'));
}

foreach ($oids as $index => $entry)
{
  $descr   = $entry['smartEnvMonTemperatureDescr'];
  $oid     = ".1.3.6.1.4.1.30826.1.4.1.1.1.3.$index";
  $current = $entry['smartEnvMonTemperatureValue'];
  $limits  = array('limit_high'      => 55,
                   'limit_low'       => 0.01,
                   'limit_high_warn' => 50,
                   'limit_low_warn'  => 5);

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'mseries-envmon', $descr, 1, $current, $limits);
}

// EOF
