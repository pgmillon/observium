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

$mib = "NETSCREEN-RESOURCES-MIB";

$mempool['used']  = snmp_get($device, "nsResMemAllocate.0", "-OvQ", $mib, mib_dirs('netscreen'));
$mempool['free']  = snmp_get($device, "nsResMemLeft.0",     "-OvQ", $mib, mib_dirs('netscreen'));
$mempool['total'] = $mempool['used'] + $mempool['free'];

// EOF
