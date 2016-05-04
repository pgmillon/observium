#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage scripts
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]).'/..');
$scriptname = basename($argv[0]);

$options = getopt("dh:");

include_once("includes/sql-config.inc.php");

if (empty($options['h']))
{
  print "Usage: scripts/rename-port-rrdfile.php -h <device_id|hostname>\n\n";
  exit(1);
}

if (is_numeric($options['h']))
{
  $where = "`device_id` = ?";
} else {
  $where = "`hostname` = ?";
}

$device = dbFetchRow("SELECT * FROM devices WHERE " . $where, array($options['h']));

$ports = dbFetchRows("SELECT * FROM ports WHERE `device_id` = ?", array($device['device_id']));

foreach ($ports as $port) {
  $old_rrdfile = trim($config['rrd_dir'])."/".trim($device['hostname'])."/port-".$port['ifIndex'].".rrd";
  $new_rrdfile = get_port_rrdfilename($device, $port);

  printf("%s -> %s\n", $old_rrdfile, $new_rrdfile);

  rename($old_rrdfile, $new_rrdfile);
}

// EOF
