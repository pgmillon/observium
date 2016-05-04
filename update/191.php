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

echo(" Migrating state sensors to status entries: ");

foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ?", array("state")) AS $sensor)
{
  $translate = array(
                     'device_id'          => 'device_id',
                     'poller_type'        => 'poller_type',
                     'sensor_oid'         => 'status_oid',
                     'sensor_index'       => 'status_index',
                     'sensor_type'        => 'status_type',
                     'sensor_descr'       => 'status_descr',
                     'entPhysicalIndex'   => 'entPhysicalIndex',
                     'entPhysicalClass'   => 'entPhysicalClass',
                     'entPhysicalIndex_measured'   => 'entPhysicalIndex_measured',
                     'measured_class'     => 'measured_class',
                     'measured_entity'    => 'measured_entity',
                     'sensor_ignore'      => 'status_ignore',
                     'sensor_disable'     => 'status_disable'
                    );

  $status_insert = array();

  foreach($translate AS $from => $to)
  {
    $status_insert[$to] = $sensor[$from];
  }

  dbInsert($status_insert, 'status');

  dbDelete('sensors', "`sensor_id` =  ?", array($sensor['sensor_id']));
  dbDelete('sensors-state', "`sensor_id` =  ?", array($sensor['sensor_id']));


  unset($status_insert, $sensor, $translate);

  echo '.';

}

echo(PHP_EOL);

// EOF
