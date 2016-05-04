<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

set_dev_attrib($device, 'poll_storage', 0);

echo(" Storage WMI: ");
foreach ($wmi['disk']['logical'] as $disk)
{
  echo(".");

  $storage_name = $disk['DeviceID'] . "\\\\ Label:" . $disk['VolumeName'] . "  Serial Number " . strtolower($disk['VolumeSerialNumber']);
  $storage_id = dbFetchCell("SELECT `storage_id` FROM `storage` WHERE `storage_descr`= ?", array($storage_name));
  $rrd_filename = "storage-host-resources-mib-" . $storage_name .".rrd";
  $used = $disk['Size'] - $disk['FreeSpace'];
  $percent = round($used / $disk['Size'] * 100);

  rrdtool_create($device, $rrd_filename, " DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U ");

  rrdtool_update($device, $rrd_filename,"N:".$used.":".$disk['FreeSpace']);
  dbUpdate(array('storage_polled' => time(), 'storage_used' => $used, 'storage_free' => $disk['FreeSpace'], 'storage_size' => $disk['Size'],
    'storage_perc' => $percent), 'storage-state', '`storage_id` = ?', array($storage_id));
}

echo(PHP_EOL);

// EOF
