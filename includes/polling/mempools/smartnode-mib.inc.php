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

$mib = 'SMARTNODE-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'memory', $cache_mempool, $mib, mib_dirs('patton'));

$index            = $mempool['mempool_index'];
$mempool['total'] = $cache_mempool[$index]['memFreeBytes'] + $cache_mempool[$index]['memAllocatedBytes'];
$mempool['used']  = $cache_mempool[$index]['memAllocatedBytes'];
$mempool['free']  = $cache_mempool[$index]['memFreeBytes'];

// EOF
