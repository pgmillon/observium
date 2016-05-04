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

$mib = 'CISCO-MEMORY-POOL-MIB';

if (!is_array($cache_storage['cisco-memory-pool-mib']))
{
  foreach (array('ciscoMemoryPoolUsed', 'ciscoMemoryPoolFree') as $oid)
  {
    $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
  }
  $cache_storage['cisco-memory-pool-mib'] = $cache_mempool;
} else {
  print_debug("Cached!");
}

$index            = $mempool['mempool_index'];
$mempool['used']  = $cache_storage['cisco-memory-pool-mib'][$index]['ciscoMemoryPoolUsed'];
$mempool['free']  = $cache_storage['cisco-memory-pool-mib'][$index]['ciscoMemoryPoolFree'];
$mempool['total'] = $mempool['used'] + $mempool['free'];

unset ($index, $oid);

// EOF
