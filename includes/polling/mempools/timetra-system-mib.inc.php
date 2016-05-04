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

$mib = 'TIMETRA-SYSTEM-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'sgiMemoryAvailable', $cache_mempool, $mib);
$cache_mempool = snmpwalk_cache_multi_oid($device, 'sgiMemoryUsed',      $cache_mempool, $mib);

$mempool['total'] = $cache_mempool[$index]['sgiMemoryAvailable'];
$mempool['used']  = $cache_mempool[$index]['sgiMemoryUsed'];

// EOF
