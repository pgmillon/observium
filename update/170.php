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

echo(' Move old GEO data if exist: ');

foreach (dbFetchRows("SELECT `device_id`, `location`, `location_lat`, `location_lon`, `location_city`, `location_county`, `location_state`, `location_country`, `location_geoapi` FROM `devices` WHERE `status` = 0 OR `disabled` = 1;") as $entry)
{
  if (is_numeric($entry['location_lat']) && is_numeric($entry['location_lon']) && $entry['location_country'])
  {
    if (!dbFetchCell("SELECT COUNT(*) FROM `devices_locations` WHERE `device_id` = ?;", array($entry['device_id'])))
    {
      // Move only from down/disabled devices
      dbInsert($entry, 'devices_locations');
      echo('.');
    }
  } else {
    echo('-');
  }
}

echo(PHP_EOL);

// EOF
