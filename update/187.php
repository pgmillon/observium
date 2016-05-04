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

$entries = array();
foreach (dbFetchRows("SELECT COUNT(`location_id`) AS `count`, `device_id` FROM `devices_locations` GROUP BY `device_id`;") as $entry)
{
  if ($entry['count'] > 1) { $entries[] = $entry['device_id']; }
}

if (count($entries))
{
  echo(' Clean duplicate geo location entries: ');
  foreach ($entries as $device_id)
  {
    // Remove all device rows except last one
    $last = dbFetchCell("SELECT MAX(`location_id`) FROM `devices_locations` WHERE `device_id` = ?;", array($device_id));
    dbDelete('devices_locations', '`device_id` = ? AND `location_id` != ?;', array($device_id, $last));
    echo('.');
  }
}

unset($entries, $device_id, $last);

echo(PHP_EOL);

// EOF
