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

$mib = 'F5-BIGIP-SYSTEM-MIB';

$tmm_mempool = snmpwalk_cache_multi_oid($device, "sysTmmStatMemoryUsed", NULL, $mib, mib_dirs('f5'));
$tmm_mempool = snmpwalk_cache_multi_oid($device, "sysTmmStatMemoryTotal", $tmm_mempool, $mib, mib_dirs('f5'));

$index = $mempool['mempool_index'];
$mempool['total'] = $tmm_mempool[$index]['sysTmmStatMemoryTotal'];
$mempool['used']  = $tmm_mempool[$index]['sysTmmStatMemoryUsed'];

// EOF
