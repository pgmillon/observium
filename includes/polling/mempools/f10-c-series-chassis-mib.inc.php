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

$mib = 'F10-C-SERIES-CHASSIS-MIB';

$index = $mempool['mempool_index'];

$cache_mempool = snmpwalk_cache_multi_oid($device, 'chRpmMemUsageUtil', $cache_mempool, $mib, mib_dirs('force10'));
if ($mempool['mempool_precision'] == 1)
{
  $mempool['total'] = 1090519040; // Hardcoded total.
} else {
  $cache_mempool    = snmpwalk_cache_multi_oid($device, 'chSysProcessorMemSize', $cache_mempool, $mib, mib_dirs('force10'));
  $mempool['total'] = $cache_mempool[$index]['chSysProcessorMemSize'];
}
$mempool['perc'] = $cache_mempool[$index]['chRpmMemUsageUtil'];

// EOF
