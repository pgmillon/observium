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

$mib = 'NETSWITCH-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'hpLocalMemTotalBytes', $cache_mempool, $mib, mib_dirs('hp'));
$cache_mempool = snmpwalk_cache_multi_oid($device, 'hpLocalMemAllocBytes', $cache_mempool, $mib, mib_dirs('hp'));

$mempool['used']  = $cache_mempool[$index]['hpLocalMemAllocBytes'];
$mempool['total'] = $cache_mempool[$index]['hpLocalMemTotalBytes'];

// EOF
