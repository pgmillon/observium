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

include_once('includes/rrdtool.inc.php');

global $rrd_pipes;

$netstat_tcp_array = dbFetchRows("SELECT `hostname` FROM `device_graphs`,`devices` WHERE `devices`.`device_id` = `device_graphs`.`device_id` AND `graph` LIKE 'netstat_tcp%';");

if (count($netstat_tcp_array))
{
  echo ' Converting RRD ds type for tcpCurrEstab COUNTER->GAUGE: ';
  rrdtool_pipe_open($rrd_process, $rrd_pipes);

  foreach ($netstat_tcp_array as $entry)
  {
    $rrd = $config['rrd_dir'] . '/' . $entry['hostname'] . '/netstats-tcp.rrd';
    rrdtool('tune', $rrd, '--data-source-type tcpCurrEstab:GAUGE');
    echo('.');
  }

  rrdtool_pipe_close($rrd_process, $rrd_pipes);
}

echo(PHP_EOL);

// EOF
