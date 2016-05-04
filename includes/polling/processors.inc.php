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

$table_rows = array();

$sql  = "SELECT `processors`.*, `processors-state`.`processor_usage`, `processors-state`.`processor_polled`";
$sql .= " FROM `processors`";
$sql .= " LEFT JOIN `processors-state` USING(`processor_id`)";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $processor)
{
  // echo("Processor " . $processor['processor_descr'] . " ");

  $file = $config['install_dir']."/includes/polling/processors/".$processor['processor_type'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    $proc = snmp_get($device, $processor['processor_oid'], "-OUQnv");
  }

  $procrrd = "processor-" . $processor['processor_type'] . "-" . $processor['processor_index'] . ".rrd";

  rrdtool_create($device, $procrrd, " \
     DS:usage:GAUGE:600:-273:1000 ");

  $proc = snmp_fix_numeric($proc);
  //list($proc) = preg_split("@\ @", $proc);
  if ($processor['processor_returns_idle'] == 1) { $proc = 100 - $proc; } // The OID returns idle value, so we subtract it from 100.

  if (!$processor['processor_precision']) { $processor['processor_precision'] = "1"; };
  $proc = round($proc / $processor['processor_precision'], 2);

  $graphs['processor'] = TRUE;

  // echo($proc . "%\n");

  // Update StatsD/Carbon
  if ($config['statsd']['enable'] == TRUE)
  {
    StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'processor'.'.'.$processor['processor_type'] . "-" . $processor['processor_index'], $proc);
  }

  // Update RRD
  rrdtool_update($device, $procrrd,"N:$proc");

  // Update SQL State
  if (is_numeric($processor['processor_polled']))
  {
    dbUpdate(array('processor_usage' => $proc, 'processor_polled' => time()), 'processors-state', '`processor_id` = ?', array($processor['processor_id']));
  } else {
    dbInsert(array('processor_id' => $processor['processor_id'], 'processor_usage' => $proc, 'processor_polled' => time()), 'processors-state');
  }

  // Check alerts

  check_entity('processor', $processor, array('processor_usage' => $proc));

    $table_row = array();
    $table_row[] = $processor['processor_descr'];
    $table_row[] = $processor['processor_type'];
    $table_row[] = $processor['processor_index'];
    $table_row[] = $processor['processor_usage'].'%';
    $table_rows[] = $table_row;
    unset($table_row);

}

$headers = array('%WLabel%n', '%WType%n', '%WIndex%n', '%WUsage%n');
print_cli_table($table_rows, $headers);

// EOF
