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

$mib = 'ONEACCESS-SYS-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'oacSysMemStatistics', $cache_mempool, $mib, mib_dirs('oneaccess'));

$index            = $mempool['mempool_index'];
$mempool['total'] = $cache_mempool[$index]['oacSysMemoryTotal'];
$mempool['used']  = $cache_mempool[$index]['oacSysMemoryAllocated'];
$mempool['free']  = $cache_mempool[$index]['oacSysMemoryFree'];

// EOF
