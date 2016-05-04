<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

print_cli_data_field("Discovering MIBs", 3);

// Include all discovery modules

$include_dir = "includes/discovery/mempools";
include("includes/include-dir-mib.inc.php");

// Remove memory pools which weren't redetected here
foreach (dbFetchRows('SELECT * FROM `mempools` WHERE `device_id` = ?', array($device['device_id'])) as $test_mempool)
{
  $mempool_index = $test_mempool['mempool_index'];
  $mempool_mib   = $test_mempool['mempool_mib'];
  $mempool_descr = $test_mempool['mempool_descr'];
  print_debug($mempool_index . " -> " . $mempool_mib);

  if (!$valid['mempool'][$mempool_mib][$mempool_index])
  {
    $GLOBALS['module_stats'][$module]['deleted']++; //echo('-');
    dbDelete('mempools', '`mempool_id` = ?', array($test_mempool['mempool_id']));
    log_event("Memory pool removed: mib $mempool_mib, index $mempool_index, descr $mempool_descr", $device, 'mempool', $test_mempool['mempool_id']);
  }
}

$GLOBALS['module_stats'][$module]['status'] = count($valid['mempool']);
if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status'])
{
  print_vars($valid['mempool']);
}

// EOF
