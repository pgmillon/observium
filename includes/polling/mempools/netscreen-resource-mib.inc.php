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

$mempool['used']  = snmp_get($device, "nsResMemAllocate.0", "-OvQ", $mib, mib_dirs('netscreen'));
$mempool['free']  = snmp_get($device, "nsResMemLeft.0",     "-OvQ", $mib, mib_dirs('netscreen'));
$mempool['total'] = $mempool['used'] + $mempool['free'];

// EOF
