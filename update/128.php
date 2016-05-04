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

echo ' Rename old storage RRDs and DB: ';

// Update storage table
dbQuery("ALTER TABLE `storage` CHANGE `storage_mib` `storage_mib` VARCHAR(64);");

foreach (dbFetchRows("SELECT `hostname`, `storage_id`, `storage_mib`, `storage_descr` FROM `storage` LEFT JOIN `devices` ON `storage`.`device_id` = `devices`.`device_id` WHERE `storage`.`storage_mib` IN ('hrstorage', 'netapp');") as $entry)
{
  if ($entry['storage_mib'] == 'hrstorage')
  {
    $old_mib = 'hrstorage';
    $new_mib = 'host-resources-mib';
  } else {
    $old_mib = 'netapp';
    $new_mib = 'netapp-mib';
  }
  $old_rrd  = $config['rrd_dir'] . "/" . $entry['hostname'] . "/" . safename("storage-" . $old_mib . "-" . safename($entry['storage_descr']) . ".rrd");
  $new_rrd  = $config['rrd_dir'] . "/" . $entry['hostname'] . "/" . safename("storage-" . $new_mib . "-" . safename($entry['storage_descr']) . ".rrd");

  if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); }
  dbUpdate(array('storage_mib' => $new_mib), 'storage', '`storage_id` = ?', array($entry['storage_id']));
  echo('.');
}

echo(PHP_EOL);

// EOF
