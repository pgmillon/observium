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

$mib = 'JUNIPER-SRX5000-SPU-MONITORING-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'jnxJsSPUMonitoringMemoryUsage', $cache_mempool, $mib, mib_dirs('junos'));

$mempool['perc'] = $cache_mempool[$index]['jnxJsSPUMonitoringMemoryUsage'];

// EOF
