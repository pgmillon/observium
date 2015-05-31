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

$mib = 'CISCO-MEMORY-POOL-MIB';

foreach (array('ciscoMemoryPoolUsed', 'ciscoMemoryPoolFree') as $oid)
{
  $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
}

$index            = $mempool['mempool_index'];
$mempool['used']  = $cache_mempool[$index]['ciscoMemoryPoolUsed'];
$mempool['free']  = $cache_mempool[$index]['ciscoMemoryPoolFree'];
$mempool['total'] = $mempool['used'] + $mempool['free'];

// EOF
