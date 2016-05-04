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

echo(' Clean incorrect manual geolocation: ');

foreach (dbFetchRows("SELECT * FROM `devices_locations` WHERE `location_manual` = '1';") as $entry)
{
  if (float_cmp($entry['location_lat'], $config['geocoding']['default']['lat'], 0.001) == 0 &&
      float_cmp($entry['location_lon'], $config['geocoding']['default']['lon'], 0.001) == 0)
  {
    dbDelete('devices_locations', 'location_id = ?', array($entry['location_id']));
    echo('.');
  }
}

echo(PHP_EOL);

// EOF
