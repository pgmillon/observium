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

if (!isset($cache_storage)) { $cache_storage = array(); } // This cache used also in storage module

$table_rows = array();

$sql  = "SELECT * FROM `mempools`";
$sql .= " LEFT JOIN `mempools-state` USING (`mempool_id`)";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $mempool)
{
  $mempool_rrd = "mempool-" . $mempool['mempool_mib'] . "-" . $mempool['mempool_index'] . ".rrd";

  $file = $config['install_dir']."/includes/polling/mempools/".$mempool['mempool_mib'].".inc.php";

  if (is_file($file))
  {
    $cache_mempool = NULL;
    $index         = $mempool['mempool_index'];

    include($file);
  } else {
    continue;
  }

  // FIXME. rename precision to scale
  if (!$mempool['mempool_precision']) { $mempool['mempool_precision'] = 1; }
  if (isset($mempool['total']) && isset($mempool['used']))
  {
    $mempool['perc']   = round($mempool['used'] / $mempool['total'] * 100, 2);
    $mempool['total'] *= $mempool['mempool_precision'];
    $mempool['used']  *= $mempool['mempool_precision'];
  }
  else if (isset($mempool['total']) && isset($mempool['free']))
  {
    $mempool['used']   = $mempool['total'] - $mempool['free'];
    $mempool['perc']   = round($mempool['used'] / $mempool['total'] * 100, 2);
    $mempool['total'] *= $mempool['mempool_precision'];
    $mempool['used']  *= $mempool['mempool_precision'];
  }
  else if (isset($mempool['total']) && isset($mempool['perc']))
  {
    $mempool['total'] *= $mempool['mempool_precision'];
    $mempool['used']   = $mempool['total'] * $mempool['perc'] / 100;
  }
  else if (isset($mempool['perc']))
  {
    $mempool['total']  = 100;
    $mempool['used']   = $mempool['perc'];
  } else {
    // Hrrmm.. it looks like empty snmp walk
    continue;
  }
  $mempool['free'] = $mempool['total'] - $mempool['used'];

  $hc = ($mempool['mempool_hc'] ? ' (HC)' : '');

  // Update StatsD/Carbon
  if ($config['statsd']['enable'] == TRUE)
  {
    StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'mempool'.'.'.$mempool['mempool_mib'] . "." . $mempool['mempool_index'].".used", $mempool['used']);
    StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'mempool'.'.'.$mempool['mempool_mib'] . "." . $mempool['mempool_index'].".free", $mempool['free']);
  }

  rrdtool_create($device, $mempool_rrd, " DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U ");
  rrdtool_update($device, $mempool_rrd,"N:".$mempool['used'].":".$mempool['free']);

  if (!is_numeric($mempool['mempool_polled'])) { dbInsert(array('mempool_id' => $mempool['mempool_id']), 'mempools-state'); }

  $mempool['state'] = array('mempool_polled' => time(),
                            'mempool_used' => $mempool['used'],
                            'mempool_perc' => $mempool['perc'],
                            'mempool_free' => $mempool['free'],
                            'mempool_total' => $mempool['total']);

  dbUpdate($mempool['state'], 'mempools-state', '`mempool_id` = ?', array($mempool['mempool_id']));
  $graphs['mempool'] = TRUE;

  check_entity('mempool', $mempool, array('mempool_perc' => $mempool['perc'], 'mempool_free' => $mempool['free'], 'mempool_used' => $mempool['used']));

//  print_message("Mempool ". $mempool['mempool_descr'] . ': '.$mempool['perc'].'%%'.$hc);

  $table_row = array();
  $table_row[] = $mempool['mempool_descr'];
  $table_row[] = $mempool['mempool_mib'];
  $table_row[] = $mempool['mempool_index'];
  $table_row[] = formatStorage($mempool['total']);
  $table_row[] = formatStorage($mempool['used']);
  $table_row[] = formatStorage($mempool['free']);
  $table_row[] = $mempool['perc'].'%';
  $table_rows[] = $table_row;
  unset($table_row);

}

$headers = array('%WLabel%n', '%WType%n', '%WIndex%n', '%WTotal%n', '%WUsed%n', '%WFree%n', '%WPerc%n');
print_cli_table($table_rows, $headers);

unset($cache_mempool, $mempool, $index, $table_row, $table_rows, $table_headers);

// EOF
