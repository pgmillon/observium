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

$devices = dbFetchRows("SELECT * FROM `devices` WHERE os='papouch'");

foreach ($devices as $device)
{
  $sensors = dbFetchRows("SELECT * FROM `sensors` WHERE device_id=?", array($device['device_id']));
  foreach ($sensors as $sensor)
  {
    switch ($sensor['sensor_oid'])
    {	
      case '.1.3.6.1.4.1.18248.1.1.1.0':
        rename_rrd($device, "sensor-temperature-papouch-tme-1.rrd", "sensor-temperature-TMESNMP2-MIB-int_temperature-0.rrd");
        break;
      case '.1.3.6.1.4.1.18248.20.1.2.1.1.2.1':
        rename_rrd($device, "sensor-temperature-papouch-th2e-1.rrd", "sensor-temperature-the_v01-MIB-inChValue-1.rrd");
        break;
      case '.1.3.6.1.4.1.18248.20.1.2.1.1.2.2':
        rename_rrd($device, "sensor-humidity-papouch-th2e-1.rrd", "sensor-humidity-the_v01-MIB-inChValue-2.rrd");
        break;
      case '.1.3.6.1.4.1.18248.20.1.2.1.1.2.3':
        rename_rrd($device, "sensor-temperature-papouch-th2e-3.rrd", "sensor-temperature-the_v01-MIB-inChValue-3.rrd");
        break;
    }
  }
}

echo(PHP_EOL);

// EOF
