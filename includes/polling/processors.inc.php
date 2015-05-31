<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$sql  = "SELECT `processors`.*, `processors-state`.processor_usage, `processors-state`.processor_polled";
$sql .= " FROM  `processors`";
$sql .= " LEFT JOIN `processors-state` ON `processors`.processor_id = `processors-state`.processor_id";
$sql .= " WHERE `device_id` = ?";

foreach (dbFetchRows($sql, array($device['device_id'])) as $processor)
{
  echo("Processor " . $processor['processor_descr'] . " ");

  $file = $config['install_dir']."/includes/polling/processors/".$processor['processor_type'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    $proc = snmp_get ($device, $processor['processor_oid'], "-O Uqnv", "\"\"");
  }

  $procrrd = "processor-" . $processor['processor_type'] . "-" . $processor['processor_index'] . ".rrd";

  rrdtool_create($device, $procrrd, " \
     DS:usage:GAUGE:600:-273:1000 ");

  $proc = trim(str_replace("\"", "", $proc));
  list($proc) = preg_split("@\ @", $proc);

  if (!$processor['processor_precision']) { $processor['processor_precision'] = "1"; };
  $proc = round($proc / $processor['processor_precision'],2);

  $graphs['processor'] = TRUE;

  echo($proc . "%\n");

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

}

?>
