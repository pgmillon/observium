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

$mib = 'AGENT-GENERAL-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'agentDRAMutilizationTotalDRAM', $cache_mempool, $mib, mib_dirs('d-link'));
$cache_mempool = snmpwalk_cache_multi_oid($device, 'agentDRAMutilizationUsedDRAM',  $cache_mempool, $mib, mib_dirs('d-link'));

$index = $mempool['mempool_index'];
$mempool['used']  = $cache_mempool[$index]['agentDRAMutilizationUsedDRAM'];
$mempool['total'] = $cache_mempool[$index]['agentDRAMutilizationTotalDRAM'];

// EOF
