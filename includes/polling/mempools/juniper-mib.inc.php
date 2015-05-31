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

$mib = 'JUNIPER-MIB';

$cache_mempool = snmpwalk_cache_multi_oid($device, 'jnxOperatingBuffer', $cache_mempool, $mib, mib_dirs('junos'));

if ($mempool['mempool_precision'] == 1)
{
  $cache_mempool    = snmpwalk_cache_multi_oid($device, 'jnxOperatingDRAMSize', $cache_mempool, $mib, mib_dirs('junos'));
  $mempool['total'] = $cache_mempool[$index]['jnxOperatingDRAMSize'];
} else {
  $cache_mempool    = snmpwalk_cache_multi_oid($device, 'jnxOperatingMemory',   $cache_mempool, $mib, mib_dirs('junos'));
  $mempool['total'] = $cache_mempool[$index]['jnxOperatingMemory'];
}

$mempool['perc'] = $cache_mempool[$index]['jnxOperatingBuffer'];

// EOF
