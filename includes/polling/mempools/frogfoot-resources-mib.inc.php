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

$mib = 'FROGFOOT-RESOURCES-MIB';

$mempool['total'] = snmp_get($device, "memTotal.0", "-OvQ", $mib, mib_dirs('ubiquiti'));
$mempool['free']  = snmp_get($device, "memFree.0", "-OvQ", $mib, mib_dirs('ubiquiti'));
$mempool['used']  = $mempool['total'] - $mempool['free'];

// EOF
