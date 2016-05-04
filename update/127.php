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

echo ' Converting FDB count RRD ds fdb->value: ';

include_once('includes/rrdtool.inc.php');

global $rrd_pipes;

rrdtool_pipe_open($rrd_process, $rrd_pipes);

foreach (dbFetchRows("SELECT hostname FROM `device_graphs`,`devices` WHERE `devices`.device_id=`device_graphs`.device_id AND graph='fdb_count';") as $entry)
{
  $rrd = $config['rrd_dir'] . '/' . $entry['hostname'] . '/fdb_count.rrd';
  rrdtool('tune', $rrd, '--data-source-rename fdb:value');
  echo('.');
}

rrdtool_pipe_close($rrd_process, $rrd_pipes);

echo(PHP_EOL);

// EOF
