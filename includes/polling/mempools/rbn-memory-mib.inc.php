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

$mib = 'RBN-MEMORY-MIB';

$mempool['used']  = snmp_get($device, ".1.3.6.1.4.1.2352.2.16.1.2.1.4.1", "-OvQ", $mib, mib_dirs());
$mempool['free']  = snmp_get($device, ".1.3.6.1.4.1.2352.2.16.1.2.1.3.1", "-OvQ", $mib, mib_dirs());
$mempool['total'] = $mempool['used'] + $mempool['free'];

// EOF
