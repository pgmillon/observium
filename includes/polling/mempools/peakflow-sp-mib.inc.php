<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

$mib = 'PEAKFLOW-SP-MIB';

$cache_mempool = snmp_get_multi($device, "devicePhysicalMemory.0 devicePhysicalMemoryInUse.0", "-OQUs", $mib);

$mempool['total'] = $cache_mempool[$index]['devicePhysicalMemory'];
$mempool['used']  = $cache_mempool[$index]['devicePhysicalMemoryInUse'];

// EOF
