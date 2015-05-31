<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Memory : ");

// Include all discovery modules

$include_dir = "includes/discovery/mempools";
include("includes/include-dir-mib.inc.php");

// Always discover host-resources-mib
include("mempools/host-resources-mib.inc.php");

if ($debug && count($valid['mempool'])) { print_vars($valid['mempool']); }

// Remove memory pools which weren't redetected here
foreach (dbFetchRows('SELECT * FROM `mempools` WHERE `device_id` = ?', array($device['device_id'])) as $test_mempool)
{
  $mempool_index = $test_mempool['mempool_index'];
  $mempool_mib   = $test_mempool['mempool_mib'];
  $mempool_descr = $test_mempool['mempool_descr'];
  print_debug($mempool_index . " -> " . $mempool_mib);

  if (!$valid['mempool'][$mempool_mib][$mempool_index])
  {
    echo('-');
    dbDelete('mempools', '`mempool_id` = ?', array($test_mempool['mempool_id']));
    log_event("Memory pool removed: mib $mempool_mib, index $mempool_index, descr $mempool_descr", $device, 'mempool', $test_mempool['mempool_id']);
  }
}

echo(PHP_EOL);

// EOF
