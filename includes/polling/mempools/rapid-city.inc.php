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

$mib = 'RAPID-CITY';

$cache_mempool = snmp_get_multi($device, "rcSysDramSize.0 rcSysDramFree.0", "-OQUs", "RAPID-CITY", mib_dirs('nortel'));

$mempool['total'] = $cache_mempool[$index]['rcSysDramSize'] * 1024;
$mempool['free']  = $cache_mempool[$index]['rcSysDramFree'];

// EOF
