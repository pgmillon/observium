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

echo(' Clean incorrect syslog entries: ');

$entries_deteted = dbDelete('syslog', "`msg` = ''");
if ($entries_deteted)
{
  echo("$entries_deteted deleted, ");
}

$devices_iosxr = dbFetchColumn('SELECT `device_id` FROM `devices` WHERE `os` = ?;', array('iosxr'));
if (count($devices_iosxr))
{
  // Fix old syslog entries for IOS-XR devices
  foreach (dbFetchRows("SELECT * FROM `syslog` WHERE `device_id` IN (".implode(',', $devices_iosxr).");") as $entry)
  {
    if (is_numeric($entry['program']))
    {
      $update_array = array('timestamp' => $entry['timestamp']);
      list(, $entry['program'], $update_array['msg']) = explode(' : ', $entry['msg'], 3);
      list(, $update_array['program']) = explode(' %', $entry['program'], 2);
      dbUpdate($update_array, 'syslog', '`seq` = ?', array($entry['seq']));
      $entries_fixed++;
    }
  }
  if ($entries_fixed)
  {
    echo("$entries_fixed fixed");
  }
}

echo(PHP_EOL);

// EOF
