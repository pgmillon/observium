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

// GBNPlatformOAM-MIB::memorySize.0 = INTEGER: 128
// GBNPlatformOAM-MIB::memoryIdle.0 = INTEGER: 51

$mib = 'GBNPlatformOAM-MIB';
echo(" $mib ");

$mempool['free']   = snmp_get($device, "memoryIdle.0", "-OQUvs", $mib, mib_dirs('gcom'));
$mempool['total']  = snmp_get($device, "memorySize.0", "-OQUvs", $mib, mib_dirs('gcom'));
$mempool['used']   = $mempool['total'] - $mempool['free'];

// EOF
