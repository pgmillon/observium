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

$mib = 'HH3C-ENTITY-EXT-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtMemUsage', $cache_mempool, $mib, mib_dirs('hh3c'));
$cache_mempool = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtMemSize',  $cache_mempool, $mib, mib_dirs('hh3c'));

$index            = $mempool['mempool_index'];
$mempool['total'] = $cache_mempool[$index]['hh3cEntityExtMemSize'];
$mempool['perc']  = $cache_mempool[$index]['hh3cEntityExtMemUsage'];

// EOF
