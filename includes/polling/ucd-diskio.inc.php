<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// FIXME - store state data in database

$diskio_data = dbFetchRows("SELECT * FROM `ucd_diskio` WHERE `device_id`  = ?",array($device['device_id']));

if (count($diskio_data))
{
  $diskio_cache = array();
  $diskio_cache = snmpwalk_cache_oid($device, "diskIOEntry", $diskio_cache, "UCD-DISKIO-MIB");

  echo("Checking UCD DiskIO MIB: ");

  foreach ($diskio_data as $diskio)
  {
    $index = $diskio['diskio_index'];

    $entry = $diskio_cache[$index];

    echo($diskio['diskio_descr'] . " ");

    if (OBS_DEBUG > 1) { print_vars($entry); }

    $rrd  = "ucd_diskio-" . $diskio['diskio_descr'] .".rrd";

    rrdtool_create($device, $rrd, " \
      DS:read:DERIVE:600:0:125000000000 \
      DS:written:DERIVE:600:0:125000000000 \
      DS:reads:DERIVE:600:0:125000000000 \
      DS:writes:DERIVE:600:0:125000000000 ");

    rrdtool_update($device, $rrd, array($entry['diskIONReadX'], $entry['diskIONWrittenX'], $entry['diskIOReads'], $entry['diskIOWrites']));
  }

  echo(PHP_EOL);
}

unset($diskio_data, $diskio_cache);

// EOF
