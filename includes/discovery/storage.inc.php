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

echo("Storage : ");

// Include all discovery modules

$include_dir = "includes/discovery/storage";
include("includes/include-dir-mib.inc.php");

if (OBS_DEBUG && count($valid['storage'])) { print_vars($valid['storage']); }

// Remove storage which weren't redetected here
$query = 'SELECT * FROM `storage` WHERE `device_id` = ?';

foreach (dbFetchRows($query, array($device['device_id'])) as $test_storage)
{
  $storage_index = $test_storage['storage_index'];
  $storage_mib   = $test_storage['storage_mib'];
  $storage_descr = $test_storage['storage_descr'];
  print_debug($storage_index . " -> " . $storage_mib);

  if (!$valid['storage'][$storage_mib][$storage_index])
  {
    echo('-');
    dbDelete('storage', 'storage_id = ?', array($test_storage['storage_id']));
    log_event("Storage removed: index $storage_index, mib $storage_mib, descr $storage_descr", $device, 'storage', $test_storage['storage_id']);
  }
}

echo(PHP_EOL);

// EOF
