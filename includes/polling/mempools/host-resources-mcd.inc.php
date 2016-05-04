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

// WARNING. This is custom poller for mcd os type..
// hrStorageSize.1 = 160481280

echo(" HOST-RESOURCES-MIB (MCD) ");

$mempool['total']  = 536870912; // 512Mb
$mempool['free']   = snmp_get($device, "hrStorageSize.1", "-OQUvs", "HOST-RESOURCES-MIB", mib_dirs());
$mempool['used']   = $mempool['total'] - $mempool['free'];

// EOF
