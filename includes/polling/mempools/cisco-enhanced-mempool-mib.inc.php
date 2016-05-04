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

$mib = 'CISCO-ENHANCED-MEMPOOL-MIB';

if ($mempool['mempool_hc'])
{
  $cemp_oid = 'cempMemPoolHC';
} else {
  $cemp_oid = 'cempMemPool';
}

if (!is_array($cache_storage['cisco-enhanced-mempool-mib']))
{
  foreach (array($cemp_oid.'Used', $cemp_oid.'Free') as $oid)
  {
    $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
    if ($device['os'] == 'iosxr' && !$GLOBALS['snmp_status'])
    {
      // Hack for some old IOS-XR, sometime return "Timeout: No Response".
      // See http://jira.observium.org/browse/OBSERVIUM-1170
      $cache_mempool = snmpwalk_cache_multi_oid($device, $oid, $cache_mempool, $mib, mib_dirs('cisco'));
    }
  }
  $cache_storage['cisco-enhanced-mempool-mib'] = $cache_mempool;
} else {
  print_debug("Cached!");
}

$index            = $mempool['mempool_index'];
$mempool['used']  = $cache_storage['cisco-enhanced-mempool-mib'][$index][$cemp_oid.'Used'];
$mempool['free']  = $cache_storage['cisco-enhanced-mempool-mib'][$index][$cemp_oid.'Free'];
$mempool['total'] = $mempool['used'] + $mempool['free'];

unset ($index, $cemp_oid, $oid);

// EOF
