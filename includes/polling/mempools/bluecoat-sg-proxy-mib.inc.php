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

$mib = 'BLUECOAT-SG-PROXY-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'sgProxyMemSysUsage',     $cache_mempool, $mib, mib_dirs('bluecoat'));
$cache_mempool = snmpwalk_cache_multi_oid($device, 'sgProxyMemAvailable', $cache_mempool, $mib, mib_dirs('bluecoat'));

$index            = $mempool['mempool_index'];
$mempool['total'] = $cache_mempool[$index]['sgProxyMemAvailable'];
$mempool['used']  = $cache_mempool[$index]['sgProxyMemSysUsage'];
$mempool['perc']  = $cache_mempool[$index]['sgProxyMemoryPressure'];

// EOF
