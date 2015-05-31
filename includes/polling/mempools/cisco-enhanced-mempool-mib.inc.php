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

$mib = 'CISCO-ENHANCED-MEMPOOL-MIB';

if ($mempool['mempool_hc'])
{
  $cemp_oid = 'cempMemPoolHC';
} else {
  $cemp_oid = 'cempMemPool';
}

foreach (array($cemp_oid.'Used', $cemp_oid.'Free') as $oid)
{
  $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
}

$index            = $mempool['mempool_index'];
$mempool['used']  = $cache_mempool[$index][$cemp_oid.'Used'];
$mempool['free']  = $cache_mempool[$index][$cemp_oid.'Free'];
$mempool['total'] = $mempool['used'] + $mempool['free'];

unset ($cemp_oid, $oid);

// EOF
