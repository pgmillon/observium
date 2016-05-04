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

$mib = 'CISCO-PROCESS-MIB';

foreach (array('cpmCPUMemoryUsed', 'cpmCPUMemoryFree') as $oid)
{
  $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
}

$index            = $mempool['mempool_index'];
$mempool['used']  = $cache_mempool[$index]['cpmCPUMemoryUsed'];
$mempool['free']  = $cache_mempool[$index]['cpmCPUMemoryFree'];
$mempool['total'] = $mempool['used'] + $mempool['free'];

// EOF
