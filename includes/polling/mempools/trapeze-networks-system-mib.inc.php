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

$mib = 'TRAPEZE-NETWORKS-SYSTEM-MIB';

$mempool['used']  = snmp_get($device, "trpzSysCpuMemoryLast5MinutesUsage.0", "-OQUvs", $mib, mib_dirs('trapeze'));
$mempool['total'] = snmp_get($device, "trpzSysCpuMemorySize.0",              "-OQUvs", $mib, mib_dirs('trapeze'));

// EOF
