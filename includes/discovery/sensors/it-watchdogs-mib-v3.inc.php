<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author Nick Schmalenberger <nick@schmalenberger.us>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

  echo(" IT-WATCHDOGS-MIB-V3 ");

  #this gets the sensors from the device
  $sensor_array = snmpwalk_cache_oid($device, 'owl.climateTable', array(), 'IT-WATCHDOGS-MIB-V3', mib_dirs('itwatchdogs'));

  #these are the 2 types of sensors we want to handle
  $sensor_type_map = array(
    'climateTempC'      => 'temperature',
    'climateHumidity'   => 'humidity',
  );

  #the sensor array has members that are climateTables which include various sensor data
  foreach ($sensor_array as $indexof_climateTables => $climateTable)
  {
    foreach ($climateTable as $oidname => $value)
    {
      #this checks if the sensor in this climateTable is one of the 2 types we want
      if ($sensor_type_map[$oidname] && is_numeric($value))
      {
        $ok      = TRUE;

        $type    = $sensor_type_map[$oidname];
        if ($type == "temperature")
        {
          #this checks if the temperature reading is within the range from the datasheet
          #http://www.itwatchdogs.com/DataSheets/MicroGoose%20datasheet%20(v1.06).pdf
          if ($value < -30 || $value > 85)
          {
            $ok = FALSE;
            echo("Temperature was out of range.\n");
          }
          #this oid is IT-WATCHDOGS-MIB-V3::climateTempC .X
          $oid=".1.3.6.1.4.1.17373.3.2.1.5".".$indexof_climateTables";
          $descr = "Degrees Celsius";
        }

        if ($type == "humidity")
        {
          #this checks if the humidity reading is within range as a percentage point
          if ($value < 0 || $value > 100)
          {
            $ok = FALSE;
            echo("Humidity was out of range.\n");
          }
          #this oid is IT-WATCHDOGS-MIB-V3::climateHumidity .X
          $oid=".1.3.6.1.4.1.17373.3.2.1.7".".$indexof_climateTables";
          $descr = "Relative Humidity";
        }

        if ($ok)
        {
          #this is what actually adds the sensor
          discover_sensor($valid['sensor'], $type, $device, $oid, $oidname, 'it-watchdogs-mib-v3', $descr, 1, $value);
        }
      }
    }
  }

unset($sensor_type_map, $oid, $sensor_array, $index, $type, $value, $descr, $ok);

// EOF
