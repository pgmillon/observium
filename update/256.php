<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$devices = dbFetchRows("SELECT * FROM `devices` WHERE os='routeros'");

foreach ($devices as $device)
{
  $sensors = dbFetchRows("SELECT * FROM `sensors` WHERE device_id=?", array($device['device_id']));
  foreach ($sensors as $sensor)
  {
    switch ($sensor['sensor_oid'])
    {
      case '1.3.6.1.4.1.14988.1.1.3.10.0':
        rename_rrd($device, "sensor-temperature-routeros-0.rrd", "sensor-temperature-MIKROTIK-MIB-mtxrHlTemperature-0.rrd");
        break;
      case '1.3.6.1.4.1.14988.1.1.3.8.0':
        rename_rrd($device, "sensor-voltage-routeros-0.rrd", "sensor-voltage-MIKROTIK-MIB-mtxrHlVoltage-0.rrd");
        break;
    }
  }
}

echo(PHP_EOL);

// EOF
